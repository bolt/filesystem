<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\IOException;
use Carbon\Carbon;
use GuzzleHttp\Stream\StreamInterface;
use League\Flysystem;
use League\Flysystem\FileNotFoundException;

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
        if ($filesystem !== null && !$filesystem instanceof FilesystemInterface) {
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

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file's timestamp as a Carbon instance.
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return Carbon The Carbon instance.
     */
    public function getCarbon()
    {
        return $this->filesystem->getCarbon($this->path);
    }

    /**
     * Read the file as a stream.
     *
     * @return StreamInterface file stream
     */
    public function readStream()
    {
        return parent::readStream();
    }

    /**
     * Write the new file.
     *
     * @param string $content
     *
     * @return void
     */
    public function write($content)
    {
        parent::write($content);
    }

    /**
     * Write the new file using a stream.
     *
     * @param StreamInterface|resource $resource
     *
     * @return void
     */
    public function writeStream($resource)
    {
        parent::writeStream($resource);
    }

    /**
     * Update the file contents.
     *
     * @param string $content
     *
     * @return void
     */
    public function update($content)
    {
        parent::update($content);
    }

    /**
     * Update the file contents with a stream.
     *
     * @param StreamInterface|resource $resource
     *
     * @return void
     */
    public function updateStream($resource)
    {
        parent::updateStream($resource);
    }

    /**
     * Create the file or update if exists.
     *
     * @param string $content
     *
     * @return void
     */
    public function put($content)
    {
        parent::put($content);
    }

    /**
     * Create the file or update if exists using a stream.
     *
     * @param StreamInterface|resource $resource
     *
     * @return void
     */
    public function putStream($resource)
    {
        parent::putStream($resource);
    }

    /**
     * Rename the file.
     *
     * @param string $newpath
     *
     * @return void
     */
    public function rename($newpath)
    {
        $this->filesystem->rename($this->path, $newpath);
        $this->path = $newpath;
    }

    /**
     * Copy the file.
     *
     * @param string $newpath
     *
     * @return File new file
     */
    public function copy($newpath)
    {
        $this->filesystem->copy($this->path, $newpath);
        return new File($this->filesystem, $newpath);
    }

    /**
     * Delete the file.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();
    }
}
