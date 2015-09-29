<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\IOException;
use Carbon\Carbon;
use GuzzleHttp\Stream\StreamInterface;
use League\Flysystem;
use League\Flysystem\FileNotFoundException;

class File extends Flysystem\File
{
    use HandlerTrait;

    /** @var FilesystemInterface */
    protected $filesystem;

    /** @var int cached timestamp */
    protected $timestamp;
    /** @var string cached mimetype */
    protected $mimetype;
    /** @var string cached visibility */
    protected $visibility;
    /** @var array cached metadata */
    protected $metadata;
    /** @var int cached size */
    protected $size;

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
     * Get the file's timestamp.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return int unix timestamp
     */
    public function getTimestamp($cache = true)
    {
        if (!$cache) {
            $this->timestamp = null;
        }
        if (!$this->timestamp) {
            $this->timestamp = parent::getTimestamp();
        }

        return $this->timestamp;
    }

    /**
     * Get the file's timestamp as a Carbon instance.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @throws FileNotFoundException
     * @throws IOException
     *
     * @return Carbon The Carbon instance.
     */
    public function getCarbon($cache = true)
    {
        return Carbon::createFromTimestamp($this->getTimestamp($cache));
    }

    /**
     * Get the file's mimetype.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return string mimetime
     */
    public function getMimetype($cache = true)
    {
        if (!$cache) {
            $this->mimetype = null;
        }
        if (!$this->mimetype) {
            $this->mimetype = parent::getMimetype();
        }

        return $this->mimetype;
    }

    /**
     * Get the file's visibility.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return string visibility
     */
    public function getVisibility($cache = true)
    {
        if (!$cache) {
            $this->visibility = null;
        }
        if (!$this->visibility) {
            $this->visibility = parent::getVisibility();
        }

        return $this->visibility;
    }

    /**
     * Get the file's metadata.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return array
     */
    public function getMetadata($cache = true)
    {
        if (!$cache) {
            $this->metadata = null;
        }
        if (!$this->metadata) {
            $this->metadata = parent::getMetadata();
        }

        return $this->metadata;
    }

    /**
     * Get the file size.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return int file size
     */
    public function getSize($cache = true)
    {
        if (!$cache) {
            $this->size = null;
        }
        if (!$this->size) {
            $this->size = parent::getSize();
        }

        return $this->size;
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
