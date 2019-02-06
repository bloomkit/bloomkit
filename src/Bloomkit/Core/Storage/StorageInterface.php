<?php

namespace Bloomkit\Core\Storage;

interface StorageInterface
{
    /**
     * Copy a file.
     *
     * @param string $path    path to the existing file
     * @param string $newpath the new path of the file
     *
     * @throws FileExistsException   thrown if $newpath exists
     * @throws FileNotFoundException thrown if $path does not exist
     *
     * @return bool true on success, false on failure
     */
    public function copy($path, $newpath);

    /**
     * Create a directory.
     *
     * @param string $dirname the name of the new directory
     * @param array  $config  an optional configuration array
     *
     * @return bool true on success, false on failure
     */
    public function createDir($dirname, array $config = []);

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     *
     * @return bool true on success, false on failure
     */
    public function delete($path);

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @throws RootViolationException thrown if $dirname is empty
     *
     * @return bool true on success, false on failure
     */
    public function deleteDir($dirname);

    /**
     * Get a file's metadata.
     *
     * @param string $path the path to the file
     *
     * @throws FileNotFoundException
     *
     * @return array|false the file metadata or false on failure
     */
    public function getMetadata($path);

    /**
     * Get a file's mime-type.
     *
     * @param string $path the path to the file
     *
     * @throws FileNotFoundException
     *
     * @return string|false the file mime-type or false on failure
     */
    public function getMimetype($path);

    /**
     * Get a file's size.
     *
     * @param string $path the path to the file
     *
     * @throws FileNotFoundException
     *
     * @return int|false the file size or false on failure
     */
    public function getSize($path);

    /**
     * Get a file's timestamp.
     *
     * @param string $path the path to the file
     *
     * @throws FileNotFoundException
     *
     * @return string|false the timestamp or false on failure
     */
    public function getTimestamp($path);

    /**
     * Get a file's visibility.
     *
     * @param string $path the path to the file
     *
     * @throws FileNotFoundException
     *
     * @return string|false the visibility (public|private) or false on failure
     */
    public function getVisibility($path);

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path);

    /**
     * List contents of a directory.
     *
     * @param string $directory the directory to list
     * @param bool   $recursive whether to list recursively
     *
     * @return array a list of file metadata
     */
    public function listContents($directory = '', $recursive = false);

    /**
     * Create a file or update if exists.
     *
     * @param string $path     the path to the file
     * @param string $contents the file contents
     * @param array  $config   an optional configuration array
     *
     * @return bool true on success, false on failure
     */
    public function put($path, $contents, array $config = []);

    /**
     * Create a file or update if exists.
     *
     * @param string   $path     the path to the file
     * @param resource $resource the file handle
     * @param array    $config   an optional configuration array
     *
     * @throws InvalidArgumentException thrown if $resource is not a resource
     *
     * @return bool true on success, false on failure
     */
    public function putStream($path, $resource, array $config = []);

    /**
     * Read a file.
     *
     * @param string $path the path to the file
     *
     * @throws FileNotFoundException
     *
     * @return string|false the file contents or false on failure
     */
    public function read($path);

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path the path to the file
     *
     * @throws FileNotFoundException
     *
     * @return resource|false the path resource or false on failure
     */
    public function readStream($path);

    /**
     * Rename a file.
     *
     * @param string $path    path to the existing file
     * @param string $newpath the new path of the file
     *
     * @throws FileExistsException   thrown if $newpath exists
     * @throws FileNotFoundException thrown if $path does not exist
     *
     * @return bool true on success, false on failure
     */
    public function rename($path, $newpath);

    /**
     * Set the visibility for a file.
     *
     * @param string $path       the path to the file
     * @param string $visibility one of 'public' or 'private'
     *
     * @throws FileNotFoundException
     *
     * @return bool true on success, false on failure
     */
    public function setVisibility($path, $visibility);

    /**
     * Update an existing file.
     *
     * @param string $path     the path of the existing file
     * @param string $contents the file contents
     * @param array  $config   an optional configuration array
     *
     * @throws FileNotFoundException
     *
     * @return bool true on success, false on failure
     */
    public function update($path, $contents, array $config = []);

    /**
     * Update an existing file using a stream.
     *
     * @param string   $path     the path of the existing file
     * @param resource $resource the file handle
     * @param array    $config   an optional configuration array
     *
     * @throws InvalidArgumentException if $resource is not a file handle
     * @throws FileNotFoundException
     *
     * @return bool true on success, false on failure
     */
    public function updateStream($path, $resource, array $config = []);

    /**
     * Write a new file.
     *
     * @param string $path     the path of the new file
     * @param string $contents the file contents
     * @param array  $config   an optional configuration array
     *
     * @throws FileExistsException
     *
     * @return bool true on success, false on failure
     */
    public function write($path, $contents, array $config = []);

    /**
     * Write a new file using a stream.
     *
     * @param string   $path     the path of the new file
     * @param resource $resource the file handle
     * @param array    $config   an optional configuration array
     *
     * @throws InvalidArgumentException if $resource is not a file handle
     * @throws FileExistsException
     *
     * @return bool true on success, false on failure
     */
    public function writeStream($path, $resource, array $config = []);
}
