<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\FilesystemInterface;
use Psr\Http\Message\StreamInterface;

/**
 * This represents a filesystem file.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class File extends BaseHandler
{
    /** @var string cached mimetype */
    protected $mimetype;
    /** @var string cached visibility */
    protected $visibility;
    /** @var int cached size */
    protected $size;

    /**
     * Helper for creating a handler from a listContents entry.
     *
     * @param FilesystemInterface $filesystem
     * @param array               $entry
     *
     * @return File
     */
    public static function createFromListingEntry(FilesystemInterface $filesystem, array $entry)
    {
        $file = new static($filesystem, $entry['path']);
        foreach (['timestamp', 'mimetype', 'visibility', 'size'] as $property) {
            if (isset($entry[$property])) {
                $file->$property = $entry[$property];
            }
        }

        return $file;
    }

    /**
     * Read the file.
     *
     * @return string
     */
    public function read()
    {
        return $this->filesystem->read($this->path);
    }

    /**
     * Read the file as a stream.
     *
     * @return StreamInterface
     */
    public function readStream()
    {
        return $this->filesystem->readStream($this->path);
    }

    /**
     * Write the new file.
     *
     * @param string $content
     */
    public function write($content)
    {
        $this->filesystem->write($this->path, $content);
    }

    /**
     * Write the new file using a stream.
     *
     * @param StreamInterface|resource $resource
     */
    public function writeStream($resource)
    {
        $this->filesystem->writeStream($this->path, $resource);
    }

    /**
     * Update the file contents.
     *
     * @param string $content
     */
    public function update($content)
    {
        $this->filesystem->update($this->path, $content);
    }

    /**
     * Update the file contents with a stream.
     *
     * @param StreamInterface|resource $resource
     */
    public function updateStream($resource)
    {
        $this->filesystem->updateStream($this->path, $resource);
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
        $this->filesystem->put($this->path, $content);
    }

    /**
     * Create the file or update if exists using a stream.
     *
     * @param StreamInterface|resource $resource
     */
    public function putStream($resource)
    {
        $this->filesystem->putStream($this->path, $resource);
    }

    /**
     * Rename the file.
     *
     * @param string $newPath
     */
    public function rename($newPath)
    {
        $this->filesystem->rename($this->path, $newPath);
        $this->path = $newPath;
    }

    /**
     * Copy the file.
     *
     * @param string $newPath
     *
     * @return File new file
     */
    public function copy($newPath)
    {
        $this->filesystem->copy($this->path, $newPath);

        return new static($this->filesystem, $newPath);
    }

    /**
     * Delete the file.
     */
    public function delete()
    {
        $this->filesystem->delete($this->path);
    }

    /**
     * Get the file's MIME Type.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return string
     */
    public function getMimeType($cache = true)
    {
        if (!$cache) {
            $this->mimetype = null;
        }
        if (!$this->mimetype) {
            $this->mimetype = $this->filesystem->getMimetype($this->path);
        }

        return $this->mimetype;
    }

    /**
     * Get the file's visibility.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return string
     */
    public function getVisibility($cache = true)
    {
        if (!$cache) {
            $this->visibility = null;
        }
        if (!$this->visibility) {
            $this->visibility = $this->filesystem->getVisibility($this->path);
        }

        return $this->visibility;
    }

    /**
     * Get the file size.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return int
     */
    public function getSize($cache = true)
    {
        if (!$cache) {
            $this->size = null;
        }
        if (!$this->size) {
            $this->size = $this->filesystem->getSize($this->path);
        }

        return $this->size;
    }

    /**
     * Get the file size in a human readable format.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return string
     */
    public function getSizeFormatted($cache = true)
    {
        $size = $this->getSize($cache);

        if ($size > 1024 * 1024) {
            return sprintf('%0.2f MiB', ($size / 1024 / 1024));
        } elseif ($size > 1024) {
            return sprintf('%0.2f KiB', ($size / 1024));
        } else {
            return $size . ' B';
        }
    }
}
