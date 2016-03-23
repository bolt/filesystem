<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Exception\IncludeFileException;
use Bolt\Filesystem\Exception\NotSupportedException;
use Psr\Http\Message\StreamInterface;

/**
 * This represents a filesystem file.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface FileInterface extends HandlerInterface
{
    /**
     * Read the file.
     *
     * @return string
     */
    public function read();

    /**
     * Read the file as a stream.
     *
     * @return StreamInterface
     */
    public function readStream();

    /**
     * Write the new file.
     *
     * @param string $content
     */
    public function write($content);

    /**
     * Write the new file using a stream.
     *
     * @param StreamInterface|resource $resource
     */
    public function writeStream($resource);

    /**
     * Update the file contents.
     *
     * @param string $content
     */
    public function update($content);

    /**
     * Update the file contents with a stream.
     *
     * @param StreamInterface|resource $resource
     */
    public function updateStream($resource);

    /**
     * Create the file or update if exists.
     *
     * @param string $content
     *
     * @return void
     */
    public function put($content);

    /**
     * Create the file or update if exists using a stream.
     *
     * @param StreamInterface|resource $resource
     */
    public function putStream($resource);

    /**
     * Rename the file.
     *
     * @param string $newPath
     */
    public function rename($newPath);

    /**
     * Get the file's MIME Type.
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Get the file size.
     *
     * @return int
     */
    public function getSize();

    /**
     * Get the file size in a human readable format.
     *
     * @param bool $si Return results according to IEC standards (ie. 4.60 KiB) or SI standards (ie. 4.7 kb)
     *
     * @return string
     */
    public function getSizeFormatted($si = false);

    /**
     * Load the PHP file.
     *
     * @param bool $once Whether to include the file only once.
     *
     * @throws NotSupportedException If the filesystem does not support including PHP files.
     * @throws IncludeFileException On failure.
     *
     * @return mixed Returns the return from the file or true if $once is true and this is a subsequent call.
     */
    public function includeFile($once = true);
}
