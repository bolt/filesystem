<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\FilesystemInterface;

/**
 * This represents a filesystem file.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class File extends BaseHandler implements FileInterface
{
    /** @var string cached mimetype */
    protected $mimetype;
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
     * {@inheritdoc}
     */
    public function read()
    {
        return $this->filesystem->read($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream()
    {
        return $this->filesystem->readStream($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function write($content)
    {
        $this->filesystem->write($this->path, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($resource)
    {
        $this->filesystem->writeStream($this->path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function update($content)
    {
        $this->filesystem->update($this->path, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($resource)
    {
        $this->filesystem->updateStream($this->path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function put($content)
    {
        $this->filesystem->put($this->path, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($resource)
    {
        $this->filesystem->putStream($this->path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($newPath)
    {
        $this->filesystem->rename($this->path, $newPath);
        $this->path = $newPath;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($newPath)
    {
        $this->filesystem->copy($this->path, $newPath);

        return new static($this->filesystem, $newPath);
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->filesystem->delete($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType($cache = true)
    {
        if (!$cache) {
            $this->mimetype = null;
        }
        if (!$this->mimetype) {
            $this->mimetype = $this->filesystem->getMimeType($this->path);
        }

        return $this->mimetype;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
