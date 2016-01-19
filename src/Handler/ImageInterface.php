<?php

namespace Bolt\Filesystem\Handler;

/**
 * This represents an image file.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface ImageInterface
{
    /**
     * Returns the info for this image.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return Image\Info
     */
    public function getInfo($cache = true);
}
