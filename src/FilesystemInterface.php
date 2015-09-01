<?php

namespace Bolt\Filesystem;

use League\Flysystem;

interface FilesystemInterface extends Flysystem\FilesystemInterface
{
    /**
     * Casts a Flysystem filesystem to this FilesystemInterface.
     *
     * @param Flysystem\FilesystemInterface $filesystem
     *
     * @return FilesystemInterface
     */
    public static function cast(Flysystem\FilesystemInterface $filesystem);

    /**
     * Get a file/directory handler.
     *
     * @param string            $path    The path to the file.
     * @param Flysystem\Handler $handler An optional existing handler to populate.
     *
     * @return File|Directory Either a file or directory handler.
     */
    public function get($path, Flysystem\Handler $handler = null);
}
