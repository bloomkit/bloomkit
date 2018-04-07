<?php
namespace Bloomkit\Core\Utilities;

/**
 * Helper functions for GUIDs
 */
class GuidUtils
{

    /**
     * Generate a GUID
     *
     * @param boolean $compressed If true, braces and dashes are removed from GUID
     * @return string
     */
    public static function generateGuid($compressed = false)
    {
        if ($compressed) {
            $pattern = '%04x%04x%04x%04x%04x%04x%04x%04x';
        } else {
            $pattern = '{%04x%04x-%04x-%04x-%04x-%04x%04x%04x}';
        }
        return strtoupper(sprintf($pattern, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)));
    }

    /**
     * Compress a GUID (remove dashes and braces)
     *
     * @param string $guid GUID to compress
     */
    public static function compressGuid($guid)
    {
        return str_replace([
            '{',
            '}',
            '-'
        ], '', $guid);
    }

    /**
     * Decompress a compressed GUID (add dashes and braces)
     *
     * @param string $guid Compressed GUIDU
     * @return string The returned format is {XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX}
     */
    public static function decompressGuid($guid)
    {
        if ($guid == NULL)
            return NULL;
        $guid = trim($guid);
        $tmpLen = strlen($guid);
        if (($tmpLen == 36) && ($guid[0] != '{') && ($guid[$tmpLen - 1] != '}'))
            $result = '{' . $guid . '}';
        else if (($tmpLen == 37) && ($guid[0] != '{'))
            $result = '{' . $guid;
        else if (($tmpLen == 37) && ($guid[$tmpLen - 1] != '}'))
            $result = $guid . '}';
        else if (($tmpLen == 32) && ($guid[0] != '{') && ($guid[$tmpLen - 1] != '}')) {
            $result = '{' . substr($guid, 0, 8) . '-' . substr($guid, 8, 4) . '-' . substr($guid, 12, 4) . '-' . substr($guid, 16, 4) . '-' . substr($guid, 20, 12) . '}';
        } else
            $result = $guid;
        return strtoupper($result);
    }
}