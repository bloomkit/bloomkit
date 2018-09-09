<?php

namespace Bloomkit\Core\Ftp;

use Bloomkit\Core\Ftp\Exception\FtpException;
use Bloomkit\Core\Exceptions\InvalidParameterException;

class FtpLoader
{
    const FTP_MODE_ASCII = 1;

    const FTP_MODE_BINARY = 2;

    const FTP_TYPE_FTP = 1;

    const FTP_TYPE_SFTP = 2;

    /**
     * @var resource
     */
    protected $connection;

    /**
     * @var string[]
     */
    protected $errors = [];

    /**
     * @var resource
     */
    protected $ftpResource;

    /**
     * @var int
     */
    protected $ftpType = self::FTP_TYPE_FTP;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string[]
     */
    protected $infos = [];

    /**
     * @var array
     */
    protected $options = ['dir' => '', 'file' => '', 'filetype' => '', 'filedatemask' => '_Ymd_His'];

    /**
     * @var string
     */
    protected $pass;

    /**
     * @var int
     */
    protected $port = 21;

    /**
     * @var string[]
     */
    protected $success = [];

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @var int
     */
    protected $timeout = 90;

    /**
     * @var string
     */
    protected $user;

    public function __construct($host, $user, $pass, $port = 21)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
    }

    public function addErrorAndLog($message)
    {
        $this->errors[] = $message;
    }

    public function addInfoAndLog($message)
    {
        $this->infos[] = $message;
    }

    public function addSuccessAndLog($message)
    {
        $this->success[] = $message;
    }

    public function createFolder($folderName)
    {
        $ftpStream = null;
        $localStream = null;

        if ($this->ftpType == FTP_TYPE_FTP) {
            if (!ftp_pasv($this->connection, true)) {
                $this->addErrorAndLog('Could not turn passive mode on! Trying anyway...');
            }

            return ftp_mkdir($this->connection, $folderName);
        } else {
            $prefix = 'ssh2.sftp://'.intval($this->ftpResource);

            return mkdir($prefix.'/'.$folderName);
        }
    }

    /**
     * Download a file from the FTP server.
     *
     * @param string $remoteFilename What to download
     * @param string $localFilename  Where to save
     * @param int    $mode           self::FTP_MODE_ASCII or self::FTP_MODE_BINARY
     *
     * @return bool Returns true if successful, false if not
     */
    public function download($remoteFilename, $localFilename, $mode = self::FTP_MODE_BINARY)
    {
        if (($mode != self::FTP_MODE_BINARY) && ($mode != self::FTP_MODE_ASCII)) {
            $this->addErrorAndLog("Invalid ftp-mode was given: $mode");

            return false;
        }

        $localStream = fopen($localFilename, 'w');
        if (is_resource($localStream) === false) {
            $this->addErrorAndLog("Failed to open local file. file=`$localFilename`");

            return false;
        }

        if ($this->ftpType == self::FTP_TYPE_FTP) {
            // FTP Connection
            if (!ftp_pasv($this->connection, true)) {
                $this->addErrorAndLog('Could not turn passive mode on! Trying anyway...');
            }

            if (!ftp_fget($this->connection, $localStream, $remoteFilename, $mode)) {
                $this->addErrorAndLog("Could not download remote file from FTP Server! file=`$remoteFilename`");

                return false;
            }

            $fileSize = ftp_size($this->connection, $remoteFilename);
        } else {
            // $remoteFilename = (substr($remoteFilename, 0, 1) != '/') ? '/'.$remoteFilename : $remoteFilename;

            $prefix = 'ssh2.sftp://'.intval($this->ftpResource);

            // SFTP Connection
            // Remote stream
            if (!$remoteStream = @fopen($prefix.$remoteFilename, 'r')) {
                $this->addErrorAndLog("Unable to open remote file: $remoteFilename");
                fclose($localStream);

                return false;
            }

            // from remote stream to local stream
            $read = 0;
            $fileSize = filesize($prefix.$remoteFilename);
            $bufSize = 8192;
            while ($read < $fileSize) {
                $rest = $fileSize - $read;
                if ($rest < $bufSize) {
                    $bufSize = $rest;
                }

                $buffer = fread($remoteStream, $bufSize);
                if ($buffer === false) {
                    $this->addErrorAndLog("reading from remote file failed. file=`$remoteFilename`");
                    fclose($localStream);
                    fclose($remoteStream);

                    return false;
                }

                // increase total bytes read
                $bytesRead = strlen($buffer);
                $read += $bytesRead;

                // write to local file
                $nBytesWritten = fwrite($localStream, $buffer, $bytesRead);

                if ($nBytesWritten === false || $nBytesWritten != $bytesRead) {
                    $this->addErrorAndLog("writing to local file failed. file=`$localFilename`");
                    fclose($localStream);
                    fclose($remoteStream);

                    return false;
                }
            }

            fclose($localStream);
            fclose($remoteStream);
        }

        if (filesize($localFilename) == 0) {
            $this->addErrorAndLog("downloaded local file is zero size. sLocalFile=`$localFilename`; sRemoteFile=`$remoteFilename`; sSizeRemoteFile=`$fileSize`");
            return false;
        }

        return true;
    }

    public function export($mapping, $sIdent)
    {
        $this->openConnection();
        $blLoggedIn = $this->login();
        $this->exportFiles($this->connection, $sIdent, $mapping);
    }

    /* Export and move local files after success
     * @param $con connection resource object
     * @param $sIdent ( i.e. sapbw-order )
     * @param $sToDir directory on FTP Server to upload files to
     * @return TRUE on success */
    protected function exportFiles($con, $sIdent, $aSettings)
    {
        if (!isset($aSettings['toDir'])) {
            $this->addErrorAndLog('aSettings[toDir] not set');

            return false;
        }

        $sToDir = $aSettings['toDir'];

        // Save filenames which where successfully downloaded/uploaded or not
        $aProcessInfo = array(
            'success' => array(),
            'error' => array(),
            'moveErrorLocal' => array(),
            'moveErrorRemote' => array(),
        );

        $sLocalDir = getShopBasePath().'export/'.$sIdent;
        $aFiles = $this->getFiles($this->connection, 'export', $sLocalDir);

        $aFilteredFiles = array();
        if ($aFiles) {
            foreach ($aFiles as $mKey => $sFile) {
                if (in_array($sFile, array(
                    '.',
                    '..',
                )) || !is_file($sLocalDir.'/'.$sFile)) {
                    continue;
                }

                $sFileName = basename($sFile);
                if ($aSettings['filename'] != '') {
                    if (stripos($sFileName, $aSettings['filename']) !== false) {
                        $aFilteredFiles[$mKey] = $sFile;
                    }
                } else {
                    $aFilteredFiles[$mKey] = $sFile;
                }
            }
        }

        if (!count($aFilteredFiles)) {
            $this->addInfoAndLog('There was nothing to upload! (no filtered files). unfilteredfilesCnt=`'.count($aFiles).'`');

            return true;
        }

        foreach ($aFilteredFiles as $sFile) {
            $sLocalFile = $sLocalDir.'/'.$sFile;
            $sLocalMoveFile = $sLocalDir.'/done/'.$sFile;

            $this->done = true;

            $sRemoteFile = $sToDir.'/temp/'.$sFile;
            $sRemoteMoveFile = $sToDir.'/'.$sFile;

            $blDone = $this->upload($sLocalFile, $sRemoteFile);
            if ($blDone === true) {
                $blSuccess = true;

                if ($this->ftpType == 'sftp') {
                    @ssh2_sftp_unlink($this->ftpResource, $sRemoteMoveFile);
                    $blMoved = ssh2_sftp_rename($this->ftpResource, $sRemoteFile, $sRemoteMoveFile);
                } else {
                    @ftp_delete($this->connection, $sRemoteMoveFile);
                    $blMoved = ftp_rename($this->connection, $sRemoteFile, $sRemoteMoveFile);
                }

                if ($blMoved == false) {
                    $blSuccess = false;
                    $aProcessInfo['moveErrorRemote'][] = $sRemoteFile;
                    $this->addErrorAndLog("remote move failed. sRemoteFile=`$sRemoteFile`; sRemoteMoveFile=`$sRemoteMoveFile`");
                }

                if (($blSuccess) && ($blMoved == false)) {
                    $blSuccess = false;
                    $aProcessInfo['moveErrorLocal'][] = $sLocalFile;
                    $this->addErrorAndLog("local move failed. file=`$sLocalFile`");
                }

                if ($blSuccess == true) {
                    $aProcessInfo['success'][] = $sLocalFile;
                    $this->addSuccessAndLog($sLocalFile);
                }
            } else {
                $aProcessInfo['error'][] = $sLocalFile;
            }
        }

        $sHowmany = 'NO';
        if (count($aProcessInfo['success']) == count($aFilteredFiles)) {
            $sHowmany = 'ALL';
        } elseif (count($aProcessInfo['success']) > 0) {
            $sHowmany = 'ONLYSOME';
        }

        $this->addInfoAndLog("Job export:$sIdent => uploaded and processed `$sHowmany` files. cnt=`".count($aProcessInfo['success']).'/'.count($aFilteredFiles).'`; see list: ', $aProcessInfo['success']);

        if (count($aProcessInfo['error'])) {
            $this->addInfoAndLog('some files could not be uploaded. cnt=`'.count($aProcessInfo['error']).'/'.count($aFilteredFiles).'`; see list: ', $aProcessInfo['error']);
        }

        if (count($aProcessInfo['moveErrorRemote'])) {
            $this->addInfoAndLog('some files could not be moved (remote). cnt=`'.count($aProcessInfo['moveErrorRemote']).'/'.count($aFilteredFiles).'`; see list: ', $aProcessInfo['moveErrorRemote']);
        }

        if (count($aProcessInfo['moveErrorLocal'])) {
            $this->addInfoAndLog('some files could not be moved (local). cnt=`'.count($aProcessInfo['moveErrorLocal']).'/'.count($aFilteredFiles).'`; see list: ', $aProcessInfo['moveErrorLocal']);
        }

        return true;
    }

    private function ftp_get_filelist($con, $path)
    {
        $aRet = [];
        $aList = ftp_nlist($con, $path);

        if (is_array($aList) && count($aList) > 0) {
            foreach ($aList as $sFile) {
                $iFileSize = ftp_size($con, $sFile);
                if ($iFileSize != -1) { // != -1 => file; == -1 => dir;
                    $aRet[] = basename($sFile);
                }
            }
        }

        return $aRet;
    }

    /* Returns file names from ftp for import action
     * and file names from local server for export
     * @param $con resource connection object
     * @param $type string 'import' or 'export'
     * @param $dir string directory name to fetch files from
     * @return array */
    protected function getFiles($con, $type, $dir = '.')
    {
        switch ($type) {
            case 'import':

                $aList = array();

                if ($this->ftpType == self::FTP_TYPE_FTP) {
                    $aList = $this->ftp_get_filelist($con, $dir);
                } else {
                    if (substr($dir, 0, 1) == '/') {
                        $dir = substr($dir, 1);
                    }

                    $prefix = 'ssh2.sftp://'.intval($this->ftpResource);

                    $dirHandle = opendir("$prefix/$dir");
                    if ($dirHandle === false) {
                        throw new FtpException("failed to open remote dir. sUrl=`$prefix/$dir`; dirHandle=`false`;");
                    } elseif ($dirHandle === null) {
                        throw new FtpException("failed to open remote dir. sUrl=`$prefix/$dir`; dirHandle=`null`;");
                    } else {
                        while (false !== ($file = readdir($dirHandle))) {
                            if ($file === null) {
                                $this->addErrorAndLog('fatal error: readdir returned `null`.');
                                break;
                            }
                            $sFileType = filetype("$prefix/$dir/$file");
                            if ($sFileType === false) {
                                $this->addErrorAndLog("failed to filetype() remote file. sUrl=`$prefix/$dir/$file`;");
                            } elseif ($sFileType == 'file') {
                                $aList[] = $file;
                            }
                        }
                    }
                }

                return $aList;
                break;
            case 'export':
                return scandir($dir);
                break;
        }

        return array();
    }

    public function getNotifyInfo()
    {
        $crlf = "\r\n";
        $notifyInfo = '';
        if (count($this->errors) > 0) {
            $notifyInfo = 'errors:'.$crlf;
            foreach ($this->errors as $error) {
                $notifyInfo .= $error.$crlf;
            }
        }

        if (count($this->infos) > 0) {
            $notifyInfo = 'infos:'.$crlf;
            foreach ($this->infos as $info) {
                $notifyInfo .= $info.$crlf;
            }
        }

        if (count($this->success) > 0) {
            $notifyInfo = 'success:'.$crlf;
            foreach ($this->success as $success) {
                $notifyInfo .= $success.$crlf;
            }
        }

        return $notifyInfo;
    }

    protected function getResponse()
    {
        $response = array(
            'code' => 0,
            'message' => '',
        );

        while (true) {
            $line = fgets($this->connection, 8129);
            $response['message'] .= $line;

            if (preg_match('/^[0-9]{3} /', $line)) {
                break;
            }
        }
        $response['code'] = intval(substr(ltrim($response['message']), 0, 3));

        return $response;
    }

    public function import($mapping, $ident)
    {
        $this->openConnection();
        $this->login();
        $this->importFiles($this->connection, $ident, $mapping);
    }

    /* Import and move files
     * @param $con connection resource object
     * @param $sIdent ( i.e. sapbw-order )
     * @param $sFromDir directory on FTP Server to fetch files from
     * @return TRUE on success */
    protected function importFiles($connection, $sIdent, $aSettings)
    {
        if (!isset($aSettings['fromDir'])) {
            $this->addErrorAndLog('aSettings[fromDir] not set');
            return false;
        }

        $fromDir = $aSettings['fromDir'];
        $remoteMove = $aSettings['remoteMove'];

        $aProcessInfo = [
            'success' => [],
            'error' => [],
            'moveErrorLocal' => [],
            'moveErrorRemote' => [],
        ];

        $localDir = rtrim($this->tempDir, '/');
        if (!file_exists($localDir)) {
            throw new InvalidParameterException('temp-directory not found: '.$localDir);
        }
        $localDir .= '/import/'.$ident;
        if ((!file_exists($localDir)) || (!file_exists($localDir.'/temp'))) {
            if (mkdir($localDir.'/temp', 0770, true) == false) {
                throw new FtpException('temp-directory not found and creation failed: '.$localDir);
            }
        }

        $tmpFiles = $this->getFiles($connection, 'import', $fromDir);

        $files = [];
        if (is_array($tmpFiles)) {
            foreach ($tmpFiles as $key => $file) {
                $fileName = basename($file);
                if ($aSettings['filename'] != '') {
                    if (stripos($fileName, $aSettings['filename']) !== false)
                        $files[$key] = $file;
                } else {
                    $files[$key] = $file;
                }
            }
        }

        if(count($files) == 0){
            $this->addInfoAndLog('There was nothing to download! (no filtered files). unfilteredfilesCnt=`'.count($tmpFiles).'`');
        } else {
            foreach ($files as $file) {
                $this->done = true;
        
                $fileName = basename($file);
                $localFile = $localDir.'/temp/'.$fileName;
                $localMoveFile = $localDir.'/'.$fileName;
                $remoteFile = $fromDir.'/'.$fileName;
                $remoteMoveFile = $fromDir.'/'.$remoteMove.'/'.$file;
        
                $success = false;
        
                if ($this->download($remoteFile, $localFile)) {
                    $success = true;
                    $moved = rename($localFile, $localMoveFile);
                    if ($moved == false) {
                        $success = false;
                        $aProcessInfo['moveErrorLocal'][] = $remoteFile;
                        $this->addErrorAndLog("local move failed: $sLocalFile");
                    } 
        
                    if ($success && $remoteMove) {
                        if ($this->ftpType == self::FTP_TYPE_SFTP) {
                            @ssh2_sftp_unlink($this->ftpResource, $remoteMoveFile);
                            $moved = ssh2_sftp_rename($this->ftpResource, $remoteFile, $remoteMoveFile);
                        } else {
                            @ftp_delete($this->connection, $remoteMoveFile);
                            $moved = ftp_rename($this->connection, $remoteFile, $remoteMoveFile);
                        }
        
                        if ($moved == false) {
                            $success = false;
                            $aProcessInfo['moveErrorRemote'][] = $sRemoteFile;
                            $this->addErrorAndLog("remote move failed from $sRemoteFile to $sRemoteMoveFile");
                        }
                    }else {
                        $aProcessInfo['error'][] = $remoteFile;
                    }
        
                    if ($success == true) {
                        $aProcessInfo['success'][] = $localMoveFile;
                        $this->addSuccessAndLog($localMoveFile);
                    }
                }
        
                $processed = 'NO';
                if (count($aProcessInfo['success']) == count($files)) {
                    $processed = 'ALL';
                } elseif (count($aProcessInfo['success']) > 0) {
                    $processed = 'SOME';
                }
        
                $this->addInfoAndLog("Job import: $ident => downloaded and processed $processed files. Count:".count($aProcessInfo['success']).'/'.count($files).'`; see list: ', $aProcessInfo['success']);
        
                if (count($aProcessInfo['error'])) {
                    $this->addInfoAndLog('some files could not be downloaded. cnt=`'.count($aProcessInfo['error']).'/'.count($aFilteredFiles).'`; see list: ', $aProcessInfo['error']);
                }
        
                if (count($aProcessInfo['moveErrorRemote'])) {
                    $this->addInfoAndLog('some files could not be moved (remote). cnt=`'.count($aProcessInfo['moveErrorRemote']).'/'.count($aFilteredFiles).'`; see list: ', $aProcessInfo['moveErrorRemote']);
                }
        
                if (count($aProcessInfo['moveErrorLocal'])) {
                    $this->addInfoAndLog('some files could not be moved (local). cnt=`'.count($aProcessInfo['moveErrorLocal']).'/'.count($aFilteredFiles).'`; see list: ', $aProcessInfo['moveErrorLocal']);
                }
            } 
        }

        return true;
    }

    /**
     * Login to the ftp-server
     *
     * @return bool Returns true if successful
     * @throws FtpException If any error occurs
     */
    public function login()
    {
        if ($this->ftpType == self::FTP_TYPE_FTP) {
            if (!ftp_login($this->connection, $this->user, $this->password)) {
                throw new FtpException("FTP Login failed, wrong credentials? Username: $this->user");
            }
        } else {
            if (!ssh2_auth_password($this->connection, $this->user, $this->password)) {
                throw new FtpException("SFTP Login failed, wrong credentials or key based authorization needed. Username: $this->user");
            }
            if (!$this->ftpResource = ssh2_sftp($this->connection)) {
                throw new FtpException('Unable to create SFTP connection.');
            }
        }
        return true;
    }

    /**
     * Connect to the ftp-server
     *
     * @result resource The connection resource
     */
    public function openConnection()
    {
        if ($this->ftpType == self::FTP_TYPE_SFTP) {
            $this->connection = ssh2_connect($this->host, $this->port);
        } else {
            $this->connection = ftp_connect($this->host, $this->port, $this->timeout);
        }

        if ($this->connection === false) {
            throw new FtpException("Failed to connect to FTP Server $this->host on port $this->port");
        }

        return $this->connection;
    }

    /**
     * Removes a file from the filesystem.
     *
     * @param string $file Path of the file to remove
     * @result bool Returns true on success, false if not
     */
    protected function removeLocalFile($file)
    {
        return unlink($file);
    }

    /**
     * Removes a file from the ftp server.
     *
     * @param string $file Path of the remote file to remove
     * @result bool Returns true on success, false if not
     */
    protected function removeRemoteFile($file)
    {
        if ($this->ftpType == self::FTP_TYPE_FTP) {
            if (ftp_delete($this->connection, $file) === true) {
                $this->addSuccessAndLog("Successfully removed $file from FTP-Server!");

                return true;
            } else {
                $this->addErrorAndLog("Failed to remove remote file from ftp server: $file");

                return false;
            }
        } else {
            if (!ssh2_sftp_unlink($this->ftpResource, $file)) {
                $this->addErrorAndLog("Failed to remove remote file from ftp server: $file");

                return false;
            } else {
                $this->addSuccessAndLog("Successfully removed $file from FTP-Server!");

                return true;
            }
        }
    }

    /**
     * Set the ftp type (self::FTP_TYPE_FTP/self::FTP_TYPE_SFTP).
     *
     * @param int $ftpType The FTP-Type to use (self::FTP_TYPE_FTP/self::FTP_TYPE_SFTP)
     */
    public function setFtpType($ftpType)
    {
        $this->ftpType = $ftpType;
    }

    /**
     * Set the temporary directory (where to temporary save downloaded files).
     *
     * @param string $tempDir The path of the temp dir
     */
    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;
    }

    /**
     * Upload a file to the FTP server.
     *
     * @param string $localFilename  What tp upload
     * @param string $remoteFilename Where to save
     * @param int    $mode           self::FTP_MODE_ASCII or self::FTP_MODE_BINARY
     *
     * @return bool Returns true if successful, false if not
     */
    public function upload($localFilename, $remoteFilename, $mode = self::FTP_MODE_BINARY)
    {
        if (($mode != self::FTP_MODE_BINARY) && ($mode != self::FTP_MODE_ASCII)) {
            $this->addErrorAndLog("Invalid ftp-mode was given: $mode");

            return false;
        }

        if ($this->ftpType == self::FTP_TYPE_FTP) {
            if (!ftp_pasv($this->connection, true)) {
                $this->addErrorAndLog('Could not activate passive mode - trying anyway...');
            }
            if (!ftp_put($this->connection, $remoteFilename, $localFilename, $mode)) {
                $this->addErrorAndLog("Unable to upload file: $localFilename");

                return false;
            }
            $fileSize = ftp_size($this->connection, $remoteFilename);
        } else {
            $prefix = 'ssh2.sftp://'.intval($this->ftpResource);
            $ftpStream = fopen($prefix.$remoteFilename, 'w');

            if (!$ftpStream) {
                $this->addErrorAndLog("Unable to open remote file: $remoteFilename");

                return false;
            }

            if (!$localStream = @fopen($localFilename, 'r')) {
                $this->addErrorAndLog("Unable to open local file: $localFilename");
                fclose($ftpStream);

                return false;
            }

            $read = 0;
            $fileSize = filesize($localFilename);
            $bufSize = 8192;
            while ($read < $fileSize) {
                $rest = $fileSize - $read;
                if ($rest < $bufSize) {
                    $bufSize = $rest;
                }

                $buffer = fread($localStream, $bufSize);
                if ($buffer === false) {
                    $this->addErrorAndLog("reading from local file failed: $localFilename");
                    fclose($localStream);
                    fclose($ftpStream);

                    return false;
                }

                $bytesRead = strlen($buffer);
                $read += $bytesRead;

                $bytesWritten = fwrite($ftpStream, $buffer, $bytesRead);

                if ($bytesWritten === false || $bytesWritten != $bytesRead) {
                    $this->addErrorAndLog("writing to remote file failed: $remoteFilename");
                    fclose($localStream);
                    fclose($ftpStream);

                    return false;
                }
            }
            fclose($localStream);
            fclose($ftpStream);
        }

        if ($fileSize == 0) {
            $sizeLocalFile = filesize($localFilename);
            $this->addErrorAndLog("uploaded remote file is zero size. Remote-file: $remoteFilename Local-file: $localFilename fileSize=$sSizeLocalFile");

            return false;
        }

        return true;
    }
}
