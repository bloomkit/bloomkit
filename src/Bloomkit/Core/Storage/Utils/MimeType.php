<?php

namespace Bloomkit\Core\Storage\Utils;

use finfo;
use ErrorException;

class MimeType
{
    protected static $extensionToMimeTypeMap = [
            '3g2' => 'video/3gpp2',
            '3gp' => 'video/3gp',
            '7zip' => 'application/x-7z-compressed',
            'aac' => 'audio/x-acc',
            'ac3' => 'audio/ac3',
            'ai' => 'application/pdf',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'au' => 'audio/x-au',
            'avi' => 'video/x-msvideo',
            'bin' => 'application/octet-stream',
            'bmp' => 'image/bmp',
            'bpmn' => 'application/octet-stream',
            'cdr' => 'application/cdr',
            'cer' => 'application/pkix-cert',
            'class' => 'application/octet-stream',
            'cpt' => 'application/mac-compactpro',
            'crl' => 'application/pkix-crl',
            'crt' => 'application/x-x509-ca-cert',
            'csr' => 'application/octet-stream',
            'css' => 'text/css',
            'csv' => 'text/x-comma-separated-values',
            'dcr' => 'application/x-director',
            'der' => 'application/x-x509-ca-cert',
            'dir' => 'application/x-director',
            'dll' => 'application/octet-stream',
            'dmn' => 'application/octet-stream',
            'dms' => 'application/octet-stream',
            'doc' => 'application/msword',
            'docm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dot' => 'application/msword',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dvi' => 'application/x-dvi',
            'dxr' => 'application/x-director',
            'eml' => 'message/rfc822',
            'eps' => 'application/postscript',
            'epub' => 'application/epub+zip',
            'exe' => 'application/octet-stream',
            'f4v' => 'video/mp4',
            'flac' => 'audio/x-flac',
            'gif' => 'image/gif',
            'gpg' => 'application/gpg-keys',
            'gtar' => 'application/x-gtar',
            'gz' => 'application/x-gzip',
            'gzip' => 'application/x-gzip',
            'hqx' => 'application/mac-binhex40',
            'htm' => 'text/html',
            'html' => 'text/html',
            'ics' => 'text/calendar',
            'jar' => 'application/java-archive',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'kdb' => 'application/octet-stream',
            'kml' => 'application/vnd.google-earth.kml+xml',
            'kmz' => 'application/vnd.google-earth.kmz',
            'latex' => 'application/x-latex',
            'lha' => 'application/octet-stream',
            'log' => 'text/plain',
            'lzh' => 'application/octet-stream',
            'm3u' => 'text/plain',
            'm4a' => 'audio/x-m4a',
            'm4u' => 'application/vnd.mpegurl',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mif' => 'application/vnd.mif',
            'mov' => 'video/quicktime',
            'movie' => 'video/x-sgi-movie',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'mpe' => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpga' => 'audio/mpeg',
            'oda' => 'application/oda',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odi' => 'application/vnd.oasis.opendocument.image',
            'odm' => 'application/vnd.oasis.opendocument.text-master',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ogg' => 'audio/ogg',
            'ott' => 'application/vnd.oasis.opendocument.text-template',
            'p10' => 'application/x-pkcs10',
            'p12' => 'application/x-pkcs12',
            'p7a' => 'application/x-pkcs7-signature',
            'p7c' => 'application/pkcs7-mime',
            'p7m' => 'application/pkcs7-mime',
            'p7r' => 'application/x-pkcs7-certreqresp',
            'p7s' => 'application/pkcs7-signature',
            'pdf' => 'application/pdf',
            'pem' => 'application/x-x509-user-cert',
            'pgp' => 'application/pgp',
            'php' => 'application/x-httpd-php',
            'php3' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'phtml' => 'application/x-httpd-php',
            'png' => 'image/png',
            'ppt' => 'application/powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ps' => 'application/postscript',
            'psd' => 'application/x-photoshop',
            'qt' => 'video/quicktime',
            'ra' => 'audio/x-realaudio',
            'ram' => 'audio/x-pn-realaudio',
            'rar' => 'application/x-rar',
            'rdf' => 'application/rdf+xml',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'rsa' => 'application/x-pkcs7',
            'rtf' => 'text/rtf',
            'rtx' => 'text/richtext',
            'rv' => 'video/vnd.rn-realvideo',
            'sea' => 'application/octet-stream',
            'shtml' => 'text/html',
            'sit' => 'application/x-stuffit',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'so' => 'application/octet-stream',
            'sst' => 'application/octet-stream',
            'svg' => 'image/svg+xml',
            'swf' => 'application/x-shockwave-flash',
            'tar' => 'application/x-tar',
            'tex' => 'application/x-tex',
            'text' => 'text/plain',
            'tgz' => 'application/x-tar',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'txt' => 'text/plain',
            'vlc' => 'application/videolan',
            'wav' => 'audio/x-wav',
            'wbxml' => 'application/wbxml',
            'webm' => 'video/webm',
            'wma' => 'audio/x-ms-wma',
            'wmlc' => 'application/wmlc',
            'wmv' => 'video/x-ms-wmv',
            'word' => 'application/msword',
            'xht' => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'xl' => 'application/excel',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xml' => 'application/xml',
            'xsl' => 'application/xml',
            'xspf' => 'application/xspf+xml',
            'z' => 'application/x-compress',
            'zip' => 'application/x-zip',
            'zsh' => 'text/x-scriptzsh',
    ];

    /**
     * Detects MIME Type based on given content.
     *
     * @param mixed $content
     *
     * @return string|null MIME Type or NULL if no mime type detected
     */
    public static function detectByContent($content)
    {
        if (!class_exists('finfo') || !is_string($content)) {
            return null;
        }
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);

            return $finfo->buffer($content) ?: null;
        } catch (ErrorException $e) {
        }
    }

    /**
     * @param string $filename
     *
     * @return string|null MIME Type or NULL if no extension detected
     */
    public static function detectByFilename($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (empty($extension)) {
            return 'text/plain';
        } else {
            return static::detectByFileExtension($extension);
        }
    }

    /**
     * Detects MIME Type based on file extension.
     *
     * @param string $extension
     *
     * @return string|null MIME Type or NULL if no extension detected
     */
    public static function detectByFileExtension($extension)
    {
        if (isset(static::$extensionToMimeTypeMap[$extension])) {
            return static::$extensionToMimeTypeMap[$extension];
        } else {
            return 'text/plain';
        }
    }

    /**
     * Guess MIME Type based on the path of the file and it's content.
     *
     * @param string          $path
     * @param string|resource $content
     *
     * @return string|null MIME Type or NULL if no extension detected
     */
    public static function guessMimeType($path, $content)
    {
        $mimeType = self::detectByContent($content);
        if (!(empty($mimeType) || in_array($mimeType, ['application/x-empty', 'text/plain', 'text/x-asm']))) {
            return $mimeType;
        }

        return MimeType::detectByFilename($path);
    }
}
