<?php

namespace Bolt\Filesystem\Tests\Handler\Image;

use Bolt\Filesystem\Handler\Image\Exif;
use PHPExif;

/**
 * Tests for Bolt\Filesystem\Image\Exif
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ExifTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $exif = new Exif([]);
        $this->assertInstanceOf('Bolt\Filesystem\Handler\Image\Exif', $exif);
    }

    public function testCast()
    {
        $exif = new Exif([]);
        $this->assertInstanceOf('Bolt\Filesystem\Handler\Image\Exif', $exif->cast(new PHPExif\Exif([])));
    }

    public function testInvalidGps()
    {
        $exif = new Exif([]);
        $this->assertFalse($exif->getLatitude());
    }

    public function testGetLatitude()
    {
        $exif = new Exif([Exif::GPS => '35.25513,149.1093073']);
        $this->assertSame(35.25513, $exif->getLatitude());
    }

    public function testGetLongitude()
    {
        $exif = new Exif([Exif::GPS => '35.25513,149.1093073']);
        $this->assertSame(149.1093073, $exif->getLongitude());
    }
}
