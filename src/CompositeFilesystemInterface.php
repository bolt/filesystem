<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\LogicException;

interface CompositeFilesystemInterface
{
    /**
     * Mount filesystems.
     *
     * @param array $filesystems [string mount point => Filesystem]
     */
    public function mountFilesystems(array $filesystems);

    /**
     * Mount a filesystem.
     *
     * @param string              $mountPoint
     * @param FilesystemInterface $filesystem
     */
    public function mountFilesystem($mountPoint, FilesystemInterface $filesystem);

    /**
     * Get the filesystem at the given mount point.
     *
     * @param string $mountPoint
     *
     * @throws LogicException If the filesystem does not exist.
     *
     * @return FilesystemInterface
     */
    public function getFilesystem($mountPoint);

    /**
     * Check if the filesystem at the given mount point exists.
     *
     * @param string $mountPoint
     *
     * @return bool
     */
    public function hasFilesystem($mountPoint);
}
