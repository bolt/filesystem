<?php

namespace Bolt\Filesystem\Handler;

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
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return string
     */
    public function getMimeType($cache = true);

    /**
     * Get the file's visibility.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return string
     */
    public function getVisibility($cache = true);

    /**
     * Get the file size.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return int
     */
    public function getSize($cache = true);

    /**
     * Get the file size in a human readable format.
     *
     * @param bool $cache Whether to use cached info from previous call
     * @param bool $fuzzy Return results according to IEC standards (ie. 4.60 KiB) or fuzzy but end-user friendly (ie. 4.7 kb)
     *
     * @return string
     */
    public function getSizeFormatted($cache = true, $fuzzy = false);
}
