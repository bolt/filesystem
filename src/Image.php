<?php

namespace Bolt\Filesystem;

class Image extends File
{
    /** @var ImageInfo */
    protected $info;

    /**
     * Returns the ImageInfo for this image.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return ImageInfo
     */
    public function getInfo($cache = true)
    {
        if (!$cache) {
            $this->info = null;
        }
        if (!$this->info) {
            $this->info = $this->filesystem->getImageInfo($this->path);
        }
        return $this->info;
    }
}
