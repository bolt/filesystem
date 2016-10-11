<?php

namespace Bolt\Filesystem\Capability;

use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Handler\Image;

/**
 * Support for getting image info.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface ImageInfo
{
    /**
     * Return the info for an image.
     *
     * @param string $path The path to the image.
     *
     * @throws IOException
     *
     * @return Image\Info
     */
    public function getImageInfo($path);
}
