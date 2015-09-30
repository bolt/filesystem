<?php

namespace Bolt\Filesystem\Image;

use PHPExif;

/**
 * Subclassing for latitude/longitude getters.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Exif extends PHPExif\Exif
{
    /**
     * Casts Exif to this sub-class.
     *
     * @param PHPExif\Exif $exif
     *
     * @return Exif
     */
    public static function cast(PHPExif\Exif $exif)
    {
        $new = new static($exif->getData());
        $new->setRawData($exif->getRawData());

        return $new;
    }

    /**
     * Returns the latitude from the GPS data, if it exists.
     *
     * @return bool|float
     */
    public function getLatitude()
    {
        return $this->getGpsPart(0);
    }

    /**
     * Returns the longitude from the GPS data, if it exists.
     *
     * @return bool|float
     */
    public function getLongitude()
    {
        return $this->getGpsPart(1);
    }

    /**
     * @param $index
     *
     * @return bool|float
     */
    private function getGpsPart($index)
    {
        $gps = $this->getGPS();
        if ($gps === false) {
            return false;
        }

        $parts = explode(',', $gps);
        if (!isset($parts[$index])) {
            return false;
        }

        return (float) $parts[$index];
    }
}
