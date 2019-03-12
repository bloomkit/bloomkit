<?php

namespace Bloomkit\Core\Utilities;

/**
 * Mutex realisation by lock-files.
 */
class Mutex
{
    /**
     * @var resource
     */
    public $fileHandle = null;

    /**
     * @var string
     */
    public $lockName = '';

    /**
     * @var int
     */
    public $timeout;

    /**
     * @var string
     */
    public $writablePath = '';

    /**
     * Constructor.
     *
     * @param string $lockName     Name of the lock
     * @param string $writablePath Path to create lock file
     * @param string $timeout      Max time tying to get a lock (no limit by default)
     */
    public function __construct($lockName, $writablePath = null, $timeout = -1)
    {
        $this->lockName = preg_replace('/[^a-z0-9_]/', '', $lockName);

        if (is_null($writablePath)) {
            $this->writablePath = $this->findWritablePath();
        } else {
            $this->writablePath = $writablePath;
        }

        $this->timeout = $timeout;
    }

    /**
     * Constructor.
     *
     * @return string x
     */
    public function findWritablePath()
    {
        $foundPath = false;

        $fileName = tempnam('/tmp', 'MUT');
        $path = dirname($fileName);
        if ($path == '/') {
            $path = '/tmp';
        }
        unlink($fileName);

        $fileHandle = fopen($path.DIRECTORY_SEPARATOR.$this->lockName, 'c');
        if ($fileHandle) {
            fclose($fileHandle);
            $foundPath = true;
        }

        if ($foundPath == false) {
            $path = '.';
            $fileHandle = fopen($path.DIRECTORY_SEPARATOR.$this->lockName, 'c');
            if ($fileHandle) {
                fclose($fileHandle);
                $foundPath = true;
            }
        }

        if ($foundPath == false) {
            throw new \Exception(get_class($this).'::'.__FUNCTION__.' failed');
        }

        return $path;
    }

    /**
     * Returns the file handle of the lock file.
     *
     * @return resource
     */
    public function getFileHandle()
    {
        if (is_null($this->fileHandle)) {
            $this->fileHandle = fopen($this->getLockFilePath(), 'c');
        }

        return $this->fileHandle;
    }

    /**
     * Try to get a lock.
     *
     * @return bool True on success or false on failure
     */
    public function getLock()
    {
        $fileHandle = $this->getFileHandle();
        $startTime = time();

        do {
            if ($this->timeout >= 0) {
                $ret = flock($fileHandle, LOCK_EX | LOCK_NB, $wouldblock);
                if ($ret == false) {
                    sleep(10);
                }
            } else {
                $ret = flock($fileHandle, LOCK_EX);
            }
        } while ((time() - $startTime) < $this->timeout && $ret == false);

        if ($ret === true) {
            touch($this->getLockFilePath());
        }

        return $ret;
    }

    /**
     * Returns path of the lock file.
     *
     * @return string Path of the lock file
     */
    public function getLockFilePath()
    {
        return $this->writablePath.DIRECTORY_SEPARATOR.$this->lockName;
    }

    /**
     * Check if there is a lock file.
     *
     * @return bool true on success or false on failure
     */
    public function isLocked()
    {
        $fileHandle = $this->getFileHandle();
        $canLock = flock($fileHandle, LOCK_EX | LOCK_NB, $wouldblock);
        if ($canLock) {
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);

            return false;
        } else {
            fclose($fileHandle);

            return true;
        }
    }

    /**
     * Release the lock.
     *
     * @return bool True on success or false on failure
     */
    public function releaseLock()
    {
        $fileHandle = $this->getFileHandle();
        $success = flock($fileHandle, LOCK_UN);
        fclose($fileHandle);

        return $success;
    }
}
