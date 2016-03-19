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
     * Returns the entree path with the mount point prefixed (if set).
     *
     * @return string
     */
    public function getFullPath();

    /**
     * Returns the directory for this entree.
     *
     * Note: If this entree is the root directory, a different
     * instance of the same directory is returned.
     * This can also be checked with {@see DirectoryInterface::isRoot}
     *
     * @return DirectoryInterface
     */
    public function getParent();

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
     * Copy the file/directory.
     *
     * By default, if the target already exists, it is only overridden if the source is newer.
     *
     * @param string    $target   Path to the target file.
     * @param bool|null $override Whether to override an existing file.
     *                            true  = always override the target.
     *                            false = never override the target.
     *                            null  = only override the target if the source is newer.
     */
    public function copy($target, $override = null);

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
     * @return int unix timestamp
     */
    public function getTimestamp();

    /**
     * Returns the entree's timestamp as a Carbon instance.
     *
     * @return Carbon The Carbon instance.
     */
    public function getCarbon();

    /**
     * Returns whether the entree's visibility is public.
     *
     * @return bool
     */
    public function isPublic();

    /**
     * Returns whether the entree's visibility is private.
     *
     * @return bool
     */
    public function isPrivate();

    /**
     * Returns the entree's visibility (public|private).
     *
     * @return string
     */
    public function getVisibility();

    /**
     * Set the visibility.
     *
     * @param string $visibility One of 'public' or 'private'.
     */
    public function setVisibility($visibility);
}
