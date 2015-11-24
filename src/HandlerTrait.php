<?php

namespace Bolt\Filesystem;

use Carbon\Carbon;
use League\Flysystem\Util;

trait HandlerTrait
{
    use MountPointAwareTrait;

    /** @var int cached timestamp */
    protected $timestamp;

    abstract public function getType();

    /**
     * Check whether the entree is a file.
     *
     * @return bool
     */
    public function isFile()
    {
        return in_array($this->getType(), ['file', 'image', 'document']);
    }

    /**
     * Check whether the entree is an image.
     *
     * @return bool
     */
    public function isImage()
    {
        return $this->getType() === 'image';
    }

    /**
     * Check whether the entree is a document.
     *
     * @return bool
     */
    public function isDocument()
    {
        return $this->getType() === 'document';
    }

    public function getPath()
    {
        return (!empty($this->mountPoint) ? $this->mountPoint . '://' : '') . $this->path;
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
     * Get the directory path.
     *
     * @return string
     */
    public function getDirname()
    {
        return Util::dirname($this->path);
    }

    /**
     * Get the filename.
     *
     * @param string $suffix If the filename ends in suffix this will also be cut off
     *
     * @return string
     */
    public function getFilename($suffix = null)
    {
        return basename($this->path, $suffix);
    }

    /**
     * Get the file/directory's timestamp.
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
            $this->timestamp = $this->filesystem->getTimestamp($this->path);
        }

        return $this->timestamp;
    }

    /**
     * Get the file/directory's timestamp as a Carbon instance.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return Carbon The Carbon instance.
     */
    public function getCarbon($cache = true)
    {
        return Carbon::createFromTimestamp($this->getTimestamp($cache));
    }
}
