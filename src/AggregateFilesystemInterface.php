<?php

namespace Bolt\Filesystem;

use LogicException;

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
     * Get the filesystem with the corresponding prefix.
     *
     * @param string $prefix
     *
     * @throws LogicException
     *
     * @return FilesystemInterface
     */
    public function getFilesystem($prefix);
}
