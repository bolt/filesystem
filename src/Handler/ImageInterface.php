<?php

namespace Bolt\Filesystem\Handler;

/**
 * This represents an image file.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface ImageInterface extends FileInterface
{
    /**
     * Returns the info for this image.
     *
     * @return Image\Info
     */
    public function getInfo();
}
