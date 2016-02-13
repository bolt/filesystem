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
        foreach (['type', 'timestamp', 'mimetype', 'visibility', 'size'] as $property) {
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
    public function copy($target, $override = null)
    {
        $this->filesystem->copy($this->path, $target, $override);

        return new static($this->filesystem, $target);
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
    public function getSizeFormatted($cache = true, $fluffy = false)
    {
        $size = $this->getSize($cache);

        if ($fluffy) {
            return $this->getSizeFormattedFluffy($size);
        } else {
            return $this->getSizeFormattedExact($size);
        }
    }

    /**
     * Format a filesize according to IEC standard. For example: '4734 bytes' -> '4.62 KiB'
     *
     * @param integer $size
     *
     * @return string
     */
    private function getSizeFormattedExact($size)
    {
        if ($size > 1024 * 1024) {
            return sprintf('%0.2f MiB', ($size / 1024 / 1024));
        } elseif ($size > 1024) {
            return sprintf('%0.2f KiB', ($size / 1024));
        } else {
            return $size . ' B';
        }
    }

    /**
     * Format a filesize as 'end user friendly', so this should be seen as something that'd
     * be used in a quick glance. For example: '4734 bytes' -> '4.7 kb'
     *
     * @param integer $size
     * @return string
     */
    private function getSizeFormattedFluffy($size)
    {
        if ($size > 1000 * 1000) {
            return sprintf('%0.1f mb', ($size / 1000 / 1000));
        } elseif ($size > 1000) {
            return sprintf('%0.1f kb', ($size / 1000));
        } else {
            return $size . ' b';
        }
    }
}
