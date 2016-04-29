<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\FileExistsException;
use Bolt\Filesystem\Exception\FileNotFoundException;
use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Exception\RootViolationException;
use Bolt\Filesystem\Handler\DirectoryInterface;
use Bolt\Filesystem\Handler\FileInterface;
use Bolt\Filesystem\Handler\HandlerInterface;
use Bolt\Filesystem\Handler\ImageInterface;
use Carbon\Carbon;
use Psr\Http\Message\StreamInterface;

/**
 * The filesystem interface.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface FilesystemInterface extends Capability\IncludeFile
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
     * @return string
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
     * @return StreamInterface
     */
    public function readStream($path);

    /**
     * Write a new file.
     *
     * @param string $path     The path of the new file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FileExistsException
     * @throws IOException
     */
    public function write($path, $contents, $config = []);

    /**
     * Write a new file using a stream.
     *
     * @param string                   $path     The path of the new file.
     * @param StreamInterface|resource $resource The stream or resource.
     * @param array                    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException If $resource is not a StreamInterface or file handle.
     * @throws FileExistsException
     * @throws IOException
     */
    public function writeStream($path, $resource, $config = []);

    /**
     * Update an existing file.
     *
     * @param string $path     The path of the existing file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function update($path, $contents, $config = []);

    /**
     * Update an existing file using a stream.
     *
     * @param string                   $path     The path of the existing file.
     * @param StreamInterface|resource $resource The stream or resource.
     * @param array                    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException If $resource is not a StreamInterface or file handle.
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function updateStream($path, $resource, $config = []);

    /**
     * Create a file or update if exists.
     *
     * @param string $path     The path to the file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws IOException
     */
    public function put($path, $contents, $config = []);

    /**
     * Create a file or update if exists.
     *
     * @param string                   $path     The path to the file.
     * @param StreamInterface|resource $resource The stream or resource.
     * @param array                    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException If $resource is not a StreamInterface or file handle.
     * @throws IOException
     */
    public function putStream($path, $resource, $config = []);

    /**
     * Read and delete a file.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string
     */
    public function readAndDelete($path);

    /**
     * Rename a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newPath The new path of the file.
     *
     * @throws FileExistsException   Thrown if $newPath exists.
     * @throws FileNotFoundException Thrown if $path does not exist.
     * @throws IOException
     */
    public function rename($path, $newPath);

    /**
     * Copy a file.
     *
     * By default, if the target already exists, it is only overridden if the source is newer.
     *
     * @param string    $origin   Path to the original file.
     * @param string    $target   Path to the target file.
     * @param bool|null $override Whether to override an existing file.
     *                            true  = always override the target.
     *                            false = never override the target.
     *                            null  = only override the target if the source is newer.
     *
     * @throws FileNotFoundException Thrown if $path does not exist.
     * @throws IOException
     */
    public function copy($origin, $target, $override = null);

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function delete($path);

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @throws RootViolationException Thrown if $dirname is empty.
     * @throws IOException
     */
    public function deleteDir($dirname);

    /**
     * Create a directory.
     *
     * @param string $dirname The name of the new directory.
     * @param array  $config  An optional configuration array.
     *
     * @throws IOException
     */
    public function createDir($dirname, $config = []);

    /**
     * Copies a directory and its contents to another.
     *
     * @param string    $originDir The origin directory
     * @param string    $targetDir The target directory
     * @param bool|null $override  Whether to override an existing file.
     *                             true  = always override the target.
     *                             false = never override the target.
     *                             null  = only override the target if the source is newer.
     */
    public function copyDir($originDir, $targetDir, $override = null);

    /**
     * Mirrors a directory to another.
     *
     * Note: By default, this will delete files in target if they are not in source.
     *
     * @param string $originDir The origin directory
     * @param string $targetDir The target directory
     * @param array  $config    Valid options are:
     *                          - delete   = Whether to delete files that are not in the source directory (default: true)
     *                          - override = See {@see copyDir}'s $override parameter for details (default: null)
     */
    public function mirror($originDir, $targetDir, $config = []);

    /**
     * Get a handler.
     *
     * @param string           $path    The path to the file.
     * @param HandlerInterface $handler An optional existing handler to populate.
     *
     * @throws IOException
     *
     * @return HandlerInterface
     */
    public function get($path, HandlerInterface $handler = null);

    /**
     * Get a file handler.
     *
     * @param string        $path    The path to the file.
     * @param FileInterface $handler An optional existing file handler to populate.
     *
     * @throws IOException
     *
     * @return FileInterface
     */
    public function getFile($path, FileInterface $handler = null);

    /**
     * Get a directory handler.
     *
     * @param string $path The path to the directory.
     *
     * @throws IOException
     *
     * @return DirectoryInterface
     */
    public function getDir($path);

    /**
     * Get a image handler.
     *
     * @param string $path The path to the file.
     *
     * @throws IOException
     *
     * @return ImageInterface
     */
    public function getImage($path);

    /**
     * Returns the type of the file.
     *
     * @param string $path The path to the file.
     *
     * @return string
     */
    public function getType($path);

    /**
     * Get a file's size.
     *
     * @param string $path The path to the file.
     *
     * @throws IOException
     *
     * @return int
     */
    public function getSize($path);

    /**
     * Get a file's unix timestamp.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string
     */
    public function getTimestamp($path);

    /**
     * Get a file's timestamp as a Carbon instance.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return Carbon
     */
    public function getCarbon($path);

    /**
     * Get a file's MIME type.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string
     */
    public function getMimeType($path);

    /**
     * Return the info for an image.
     *
     * @param string $path The path to the file.
     *
     * @throws IOException
     *
     * @return Handler\Image\Info
     */
    public function getImageInfo($path);

    /**
     * Get a file's visibility (public|private).
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return string
     */
    public function getVisibility($path);

    /**
     * Set the visibility for a file.
     *
     * @param string $path       The path to the file.
     * @param string $visibility One of 'public' or 'private'.
     *
     * @throws IOException
     */
    public function setVisibility($path, $visibility);

    /**
     * List contents of a directory.
     *
     * @param string $directory The directory to list.
     * @param bool   $recursive Whether to list recursively.
     *
     * @throws IOException
     *
     * @return HandlerInterface[]
     */
    public function listContents($directory = '', $recursive = false);

    /**
     * Returns a finder instance. Let's find some files!
     *
     * @return Finder
     */
    public function find();

    /**
     * Register a plugin.
     *
     * @param PluginInterface $plugin The plugin to register.
     */
    public function addPlugin(PluginInterface $plugin);
}
