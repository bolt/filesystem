<?php

namespace Bolt\Filesystem;

use League\Flysystem\Util;

trait HandlerTrait
{
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
}
