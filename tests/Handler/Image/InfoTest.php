<?php

namespace Bolt\Filesystem\Tests\Handler\Image;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\Handler\Image;

/**
 * Tests for Bolt\Filesystem\Image\Info
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class InfoTest extends \PHPUnit_Framework_TestCase
{
    /** @var Filesystem */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->filesystem = new Filesystem(new Local(__DIR__ . '/../../'));
    }

    public function testConstruct()
    {
        $exif = new Image\Exif([]);
        $type = Image\Type::getById(IMAGETYPE_JPEG);
        $info = new Image\Info(new Image\Dimensions(1024, 768), $type, 2, 7, 'Marcel Marceau', $exif);
        $this->assertInstanceOf(Image\Info::class, $info);
    }

    public function testCreateFromFile()
    {
        $file = dirname(dirname(__DIR__)) . '/fixtures/images/1-top-left.jpg';
        $info = Image\Info::createFromFile($file);

        $this->assertInstanceOf(Image\Info::class, $info);
        $this->assertInstanceOf(Image\Type::class, $info->getType());
        $this->assertInstanceOf(Image\Exif::class, $info->getExif());

        $this->assertSame(400, $info->getWidth());
        $this->assertSame(200, $info->getHeight());
        $this->assertSame(8, $info->getBits());
        $this->assertSame(3, $info->getChannels());
        $this->assertSame('image/jpeg', $info->getMime());
        $this->assertSame(2, $info->getAspectRatio());

        $this->assertTrue($info->isLandscape());
        $this->assertFalse($info->isPortrait());
        $this->assertFalse($info->isSquare());
    }

    public function testCreateFromFileFail()
    {
        $this->setExpectedException(IOException::class, 'Failed to get image data from file');
        Image\Info::createFromFile('drop-bear.jpg');
    }

    public function testCreateFromString()
    {
        $file = $this->filesystem->getFile('fixtures/images/1-top-left.jpg')->read();
        $info = Image\Info::createFromString($file);

        $this->assertInstanceOf(Image\Info::class, $info);
        $this->assertInstanceOf(Image\Type::class, $info->getType());
        $this->assertInstanceOf(Image\Exif::class, $info->getExif());

        $this->assertSame(400, $info->getWidth());
        $this->assertSame(200, $info->getHeight());
        $this->assertSame(8, $info->getBits());
        $this->assertSame(3, $info->getChannels());
        $this->assertSame('image/jpeg', $info->getMime());
        $this->assertSame(2, $info->getAspectRatio());

        $this->assertTrue($info->isLandscape());
        $this->assertFalse($info->isPortrait());
        $this->assertFalse($info->isSquare());
    }

    public function testCreateFromStringFail()
    {
        $this->setExpectedException(IOException::class, 'Failed to get image data from string');
        Image\Info::createFromString('drop-bear.jpg');
    }

    public function testClone()
    {
        $file = $this->filesystem->getFile('fixtures/images/1-top-left.jpg')->read();
        $info = Image\Info::createFromString($file);
        $clone = clone $info;

        $this->assertNotSame($clone->getExif(), $info->getExif());
    }
}
