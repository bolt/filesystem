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
     * @return Image\Info
     */
    public function getInfo();
}
