<?php

namespace Bolt\Filesystem;

use Carbon\Carbon;
use League\Flysystem;
use League\Flysystem\FileNotFoundException;

interface FilesystemInterface extends Flysystem\FilesystemInterface
{
    /**
     * Get a file/directory handler.
     *
     * @param string            $path    The path to the file.
     * @param Flysystem\Handler $handler An optional existing handler to populate.
     *
     * @return File|Directory Either a file or directory handler.
     */
    public function get($path, Flysystem\Handler $handler = null);

    /**
     * Get a image handler.
     *
     * @param string $path The path to the file
     *
     * @return Image
     */
    public function getImage($path);

    /**
     * Return the ImageInfo for an image.
     *
     * @param string $path The path to the file
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
     * @throws \RuntimeException
     *
     * @return Carbon The Carbon instance
     */
    public function getCarbon($path);
}
