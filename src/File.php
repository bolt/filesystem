<?php

namespace Bolt\Filesystem;

use League\Flysystem;

class File extends Flysystem\File
{
    /** @var FilesystemInterface */
    protected $filesystem;

    /**
     * Constructor.
     *
     * @param Flysystem\FilesystemInterface $filesystem
     * @param string                        $path
     */
    public function __construct(Flysystem\FilesystemInterface $filesystem = null, $path = null)
    {
        if (!$filesystem instanceof FilesystemInterface) {
            $filesystem = Filesystem::cast($filesystem);
        }
        parent::__construct($filesystem, $path);
    }

    /**
     * Casts a Flysystem\File to this subclass.
     *
     * @param Flysystem\File $file
     *
     * @return File
     */
    public static function cast(Flysystem\File $file)
    {
        return new static($file->getFilesystem(), $file->getPath());
    }

    /**
     * Set the Filesystem object.
     *
     * @param Flysystem\FilesystemInterface $filesystem
     *
     * @return File
     */
    public function setFilesystem(Flysystem\FilesystemInterface $filesystem)
    {
        if (!$filesystem instanceof FilesystemInterface) {
            $filesystem = Filesystem::cast($filesystem);
        }
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Retrieve the Filesystem object.
     *
     * @return FilesystemInterface
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }
}