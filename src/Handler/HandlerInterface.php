<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\MountPointAwareInterface;
use Carbon\Carbon;

/**
 * This represents a filesystem entree.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface HandlerInterface extends MountPointAwareInterface
{
    /**
     * Set the Filesystem object.
     *
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem);

    /**
     * Returns the Filesystem object.
     *
     * @return FilesystemInterface
     */
    public function getFilesystem();

    /**
     * Set the entree path.
     *
     * @param string $path
     */
    public function setPath($path);

    /**
     * Returns the entree path.
     *
     * @return string path
     */
    public function getPath();

    /**
     * Returns whether the entree exists.
     *
     * @return bool
     */
    public function exists();

    /**
     * Delete the entree.
     */
    public function delete();

    /**
     * Returns whether the entree is a directory.
     *
     * @return bool
     */
    public function isDir();

    /**
     * Returns whether the entree is a file.
     *
     * @return bool
     */
    public function isFile();

    /**
     * Returns whether the entree is a image.
     *
     * @return bool
     */
    public function isImage();

    /**
     * Returns whether the entree is a document.
     *
     * @return bool
     */
    public function isDocument();

    /**
     * Returns the entree's type (file|dir|image|document).
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the file extension.
     *
     * @return string
     */
    public function getExtension();

    /**
     * Returns the entree's directory's path.
     *
     * @return string
     */
    public function getDirname();

    /**
     * Returns the filename.
     *
     * @param string $suffix If the filename ends in suffix this will also be cut off
     *
     * @return string
     */
    public function getFilename($suffix = null);

    /**
     * Returns the entree's timestamp.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return int unix timestamp
     */
    public function getTimestamp($cache = true);

    /**
     * Returns the entree's timestamp as a Carbon instance.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return Carbon The Carbon instance.
     */
    public function getCarbon($cache = true);
}
