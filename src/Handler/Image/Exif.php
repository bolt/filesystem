<?php

namespace Bolt\Filesystem\Handler\Image;

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
     * Returns the aspect ratio.
     *
     * @return float
     */
    public function getAspectRatio()
    {
        if ($this->getWidth() == 0 || $this->getHeight() == 0) {
            return 0.0;
        }

        // Account for image rotation
        if (in_array($this->getOrientation(), [5, 6, 7, 8])) {
            return $this->getHeight() / $this->getWidth();
        }

        return $this->getWidth() / $this->getHeight();
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

    /**
     * Returns the creation datetime, if it exists.
     *
     * @deprecated Use {@see Exif::getCreationDate} instead.
     *
     * @return bool|\DateTime
     */
    public function getDateTime()
    {
        return $this->getCreationDate();
    }
}
