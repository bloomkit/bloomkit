<?php

namespace Bloomkit\Core\Storage\Utils;

use LogicException;

class Utils
{
    /**
     * Returns the trailing name component of the path.
     *
     * @param string $path
     *
     * @return string
     */
    private static function basename($path)
    {
        $separators = DIRECTORY_SEPARATOR === '/' ? '/' : '\/';
        $path = rtrim($path, $separators);
        $basename = preg_replace('#.*?([^'.preg_quote($separators, '#').']+$)#', '$1', $path);
        if (DIRECTORY_SEPARATOR === '/') {
            return $basename;
        }
        while (preg_match('#^[a-zA-Z]{1}:[^\\\/]#', $basename)) {
            $basename = substr($basename, 2);
        }
        // Remove colon for standalone drive letter names.
        if (preg_match('#^[a-zA-Z]{1}:$#', $basename)) {
            $basename = rtrim($basename, ':');
        }

        return $basename;
    }

    /**
     * Get content size.
     *
     * @param string $contents
     *
     * @return int content size
     */
    public static function contentSize($contents)
    {
        if (defined('MB_OVERLOAD_STRING')) {
            return mb_strlen($contents, '8bit');
        } else {
            return strlen($contents);
        }
    }

    /**
     * Get a normalized dirname from a path.
     *
     * @param string $path
     *
     * @return string dirname
     */
    public static function dirname($path)
    {
        return static::normalizeDirname(dirname($path));
    }

    /**
     * Emulate directories.
     *
     * @param array $listing
     *
     * @return array listing with emulated directories
     */
    public static function emulateDirectories(array $listing)
    {
        $directories = [];
        $listedDirectories = [];
        foreach ($listing as $object) {
            list($directories, $listedDirectories) = static::emulateObjectDirectories($object, $directories, $listedDirectories);
        }
        $directories = array_diff(array_unique($directories), array_unique($listedDirectories));
        foreach ($directories as $directory) {
            $listing[] = static::pathinfo($directory) + ['type' => 'dir'];
        }

        return $listing;
    }

    /**
     * Emulate the directories of a single object.
     *
     * @param array $object
     * @param array $directories
     * @param array $listedDirectories
     *
     * @return array
     */
    protected static function emulateObjectDirectories(array $object, array $directories, array $listedDirectories)
    {
        if ($object['type'] === 'dir') {
            $listedDirectories[] = $object['path'];
        }
        if (empty($object['dirname'])) {
            return [$directories, $listedDirectories];
        }
        $parent = $object['dirname'];
        while (!empty($parent) && !in_array($parent, $directories)) {
            $directories[] = $parent;
            $parent = static::dirname($parent);
        }
        if (isset($object['type']) && $object['type'] === 'dir') {
            $listedDirectories[] = $object['path'];

            return [$directories, $listedDirectories];
        }

        return [$directories, $listedDirectories];
    }

    /**
     * Get the size of a stream.
     *
     * @param resource $resource
     *
     * @return int stream size
     */
    public static function getStreamSize($resource)
    {
        $stat = fstat($resource);

        return $stat['size'];
    }

    public static function isSeekableStream($resource)
    {
        $metadata = stream_get_meta_data($resource);

        return $metadata['seekable'];
    }

    /**
     * Map result arrays.
     *
     * @param array $object
     * @param array $map
     *
     * @return array mapped result
     */
    public static function map(array $object, array $map)
    {
        $result = [];
        foreach ($map as $from => $to) {
            if (!isset($object[$from])) {
                continue;
            }
            $result[$to] = $object[$from];
        }

        return $result;
    }

    /**
     * Normalize a dirname return value.
     *
     * @param string $dirname
     *
     * @return string normalized dirname
     */
    public static function normalizeDirname($dirname)
    {
        return $dirname === '.' ? '' : $dirname;
    }

    /**
     * Normalize path.
     *
     * @param string $path
     *
     * @throws LogicException
     *
     * @return string
     */
    public static function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);

        //Removes unprintable characters and invalid unicode characters.
        while (preg_match('#\p{C}+|^\./#u', $path)) {
            $path = preg_replace('#\p{C}+|^\./#u', '', $path);
        }

        $parts = [];
        foreach (explode('/', $path) as $part) {
            switch ($part) {
                case '':
                case '.':
                    break;
                case '..':
                    if (empty($parts)) {
                        throw new \LogicException('Path is outside of the defined root, path: ['.$path.']');
                    }
                    array_pop($parts);
                    break;
                default:
                    $parts[] = $part;
                    break;
            }
        }

        return implode('/', $parts);
    }

    /**
     * Normalize prefix.
     *
     * @param string $prefix
     * @param string $separator
     *
     * @return string normalized path
     */
    public static function normalizePrefix($prefix, $separator)
    {
        return rtrim($prefix, $separator).$separator;
    }

    /**
     * Get normalized pathinfo.
     *
     * @param string $path
     *
     * @return array pathinfo
     */
    public static function pathinfo($path)
    {
        $pathinfo = compact('path');
        if ('' !== $dirname = dirname($path)) {
            $pathinfo['dirname'] = $dirname === '.' ? '' : $dirname;
        }
        $pathinfo['basename'] = static::basename($path);
        $pathinfo += pathinfo($pathinfo['basename']);

        return $pathinfo + ['dirname' => ''];
    }

    /**
     * Rewind a stream.
     *
     * @param resource $resource
     */
    public static function rewindStream($resource)
    {
        if (ftell($resource) !== 0 && static::isSeekableStream($resource)) {
            rewind($resource);
        }
    }
}
