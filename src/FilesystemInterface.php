<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\FileExistsException;
use Bolt\Filesystem\Exception\FileNotFoundException;
use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Exception\RootViolationException;
use Carbon\Carbon;
use InvalidArgumentException;
use League\Flysystem;

interface FilesystemInterface extends Flysystem\FilesystemInterface
{
    /**
     * Check whether a file exists.
     *
     * @param string $path The path to the file.
     *
     * @return bool
     */
    public function has($path);

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string The file contents.
     */
    public function read($path);

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return resource The path resource.
     */
    public function readStream($path);

    /**
     * List contents of a directory.
     *
     * @param string $directory The directory to list.
     * @param bool   $recursive Whether to list recursively.
     *
     * @throws IOException
     *
     * @return array A list of file metadata.
     */
    public function listContents($directory = '', $recursive = false);

    /**
     * Get a file's metadata.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return array The file metadata.
     */
    public function getMetadata($path);

    /**
     * Get a file's size.
     *
     * @param string $path The path to the file.
     *
     * @throws IOException
     *
     * @return int The file size.
     */
    public function getSize($path);

    /**
     * Get a file's mime-type.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string The file mime-type.
     */
    public function getMimetype($path);

    /**
     * Get a file's timestamp.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string The unix timestamp.
     */
    public function getTimestamp($path);

    /**
     * Get a file's visibility.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string The visibility (public|private).
     */
    public function getVisibility($path);

    /**
     * Write a new file.
     *
     * @param string $path     The path of the new file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FileExistsException
     * @throws IOException
     *
     * @return void
     */
    public function write($path, $contents, array $config = []);

    /**
     * Write a new file using a stream.
     *
     * @param string   $path     The path of the new file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException If $resource is not a file handle.
     * @throws FileExistsException
     * @throws IOException
     *
     * @return void
     */
    public function writeStream($path, $resource, array $config = []);

    /**
     * Update an existing file.
     *
     * @param string $path     The path of the existing file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return void
     */
    public function update($path, $contents, array $config = []);

    /**
     * Update an existing file using a stream.
     *
     * @param string   $path     The path of the existing file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException If $resource is not a file handle.
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return void
     */
    public function updateStream($path, $resource, array $config = []);

    /**
     * Rename a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws FileExistsException   Thrown if $newpath exists.
     * @throws FileNotFoundException Thrown if $path does not exist.
     * @throws IOException
     *
     * @return void
     */
    public function rename($path, $newpath);

    /**
     * Copy a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws FileExistsException   Thrown if $newpath exists.
     * @throws FileNotFoundException Thrown if $path does not exist.
     * @throws IOException
     *
     * @return void
     */
    public function copy($path, $newpath);

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return void
     */
    public function delete($path);

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @throws RootViolationException Thrown if $dirname is empty.
     * @throws IOException
     *
     * @return void
     */
    public function deleteDir($dirname);

    /**
     * Create a directory.
     *
     * @param string $dirname The name of the new directory.
     * @param array  $config  An optional configuration array.
     *
     * @throws IOException
     *
     * @return void
     */
    public function createDir($dirname, array $config = []);

    /**
     * Set the visibility for a file.
     *
     * @param string $path       The path to the file.
     * @param string $visibility One of 'public' or 'private'.
     *
     * @throws IOException
     *
     * @return void
     */
    public function setVisibility($path, $visibility);

    /**
     * Create a file or update if exists.
     *
     * @param string $path     The path to the file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws IOException
     *
     * @return void
     */
    public function put($path, $contents, array $config = []);

    /**
     * Create a file or update if exists.
     *
     * @param string   $path     The path to the file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException Thrown if $resource is not a resource.
     * @throws IOException
     *
     * @return void
     */
    public function putStream($path, $resource, array $config = []);

    /**
     * Read and delete a file.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string The file contents.
     */
    public function readAndDelete($path);

    /**
     * Get a file/directory handler.
     *
     * @param string            $path    The path to the file.
     * @param Flysystem\Handler $handler An optional existing handler to populate.
     *
     * @throws IOException
     *
     * @return File|Directory Either a file or directory handler.
     */
    public function get($path, Flysystem\Handler $handler = null);

    /**
     * Get a image handler.
     *
     * @param string $path The path to the file.
     *
     * @throws IOException
     *
     * @return Image
     */
    public function getImage($path);

    /**
     * Return the ImageInfo for an image.
     *
     * @param string $path The path to the file.
     *
     * @throws IOException
     *
     * @return ImageInfo
     */
    public function getImageInfo($path);

    /**
     * Get a file's timestamp as a Carbon instance.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return Carbon The Carbon instance.
     */
    public function getCarbon($path);
}
