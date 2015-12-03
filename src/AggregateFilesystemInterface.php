<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\LogicException;

interface AggregateFilesystemInterface
{
    /**
     * Mount filesystems.
     *
     * @param array $filesystems [prefix => Filesystem]
     *
     * @return $this
     */
    public function mountFilesystems(array $filesystems);

    /**
     * Mount a filesystem.
     *
     * @param string              $prefix
     * @param FilesystemInterface $filesystem
     *
     * @return $this
     */
    public function mountFilesystem($prefix, FilesystemInterface $filesystem);

    /**
     * Get the filesystem with the given prefix.
     *
     * @param string $prefix
     *
     * @throws LogicException If the filesystem does not exist.
     *
     * @return FilesystemInterface
     */
    public function getFilesystem($prefix);

    /**
     * Check if the filesystem with the given prefix exists.
     *
     * @param string $prefix
     *
     * @return bool
     */
    public function hasFilesystem($prefix);
}
