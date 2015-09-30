<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\IOException;
use League\Flysystem;

class Directory extends Flysystem\Directory
{
    use HandlerTrait;

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
     * Casts a Flysystem\Directory to this subclass.
     *
     * @param Flysystem\Directory $directory
     *
     * @return Directory
     */
    public static function cast(Flysystem\Directory $directory)
    {
        return new static($directory->getFilesystem(), $directory->getPath());
    }

    /**
     * Set the Filesystem object.
     *
     * @param Flysystem\FilesystemInterface $filesystem
     *
     * @return Directory
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

    /**
     * Get a file/directory handler.
     *
     * @param string            $path    The path to the file.
     * @param Flysystem\Handler $handler An optional existing handler to populate.
     *
     * @throws IOException
     *
     * @return File|Directory Either a file or directory handler.
     */
    public function get($path, Flysystem\Handler $handler = null)
    {
        return $this->filesystem->get($this->path . '/' . $path, $handler);
    }

    /**
     * Check whether the directory exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->filesystem->has($this->path);
    }

    /**
     * List the directory contents.
     *
     * @param bool $recursive
     *
     * @return File[]|Directory[]|Image[] A list of handlers.
     */
    public function getContents($recursive = false)
    {
        return parent::getContents($recursive);
    }
}
