<?php

namespace Bolt\Filesystem;

class Image extends File
{
    /** @var Image\Info */
    protected $info;

    /**
     * Returns the info for this image.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return Image\Info
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
