<?php

namespace Bolt\Filesystem\Tests\Handler\Image;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\Handler\Image;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Bolt\Filesystem\Image\Info
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class InfoTest extends TestCase
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
        new Image\Info(new Image\Dimensions(1024, 768), $type, 2, 7, 'Marcel Marceau', $exif);
    }

    public function testCreateFromFile()
    {
        $file = dirname(dirname(__DIR__)) . '/fixtures/images/1-top-left.jpg';
        $info = Image\Info::createFromFile($file);

        $this->assertInstanceOf(Image\Info::class, $info);
        $this->assertInstanceOf(Image\TypeInterface::class, $info->getType());
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
        $this->assertTrue($info->isValid());
    }

    public function testCreateFromFileEmpty()
    {
        $info = Image\Info::createFromFile(__DIR__ . '/../../fixtures2/empty.jpg');

        $this->assertSame(0, $info->getWidth());
        $this->assertSame(0, $info->getHeight());
        $this->assertSame(0, $info->getBits());
        $this->assertSame(0, $info->getChannels());
        $this->assertSame(null, $info->getMime());
        $this->assertSame(0.0, $info->getAspectRatio());
        $this->assertFalse($info->isValid());
    }

    public function testCreateFromFileInvalid()
    {
        $info = Image\Info::createFromFile('drop-bear.jpg');

        $this->assertFalse($info->isValid());
    }

    public function testCreateFromString()
    {
        $file = $this->filesystem->getFile('fixtures/images/1-top-left.jpg')->read();
        $info = Image\Info::createFromString($file);

        $this->assertInstanceOf(Image\Info::class, $info);
        $this->assertInstanceOf(Image\TypeInterface::class, $info->getType());
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
        $this->assertTrue($info->isValid());
    }

    public function testCreateFromStringEmpty()
    {
        $file = $this->filesystem->getFile('fixtures2/empty.jpg');

        $info = Image\Info::createFromString($file->read(), $file->getPath());

        $this->assertSame(0, $info->getWidth());
        $this->assertSame(0, $info->getHeight());
        $this->assertSame(0, $info->getBits());
        $this->assertSame(0, $info->getChannels());
        $this->assertSame(null, $info->getMime());
        $this->assertSame(0.0, $info->getAspectRatio());
        $this->assertFalse($info->isValid());
    }

    public function testCreateFromStringInvalid()
    {
        $info = Image\Info::createFromString('drop-bear.jpg');

        $this->assertFalse($info->isValid());
    }

    public function testClone()
    {
        $file = $this->filesystem->getFile('fixtures/images/1-top-left.jpg')->read();
        $info = Image\Info::createFromString($file);
        $clone = clone $info;

        $this->assertNotSame($clone->getExif(), $info->getExif());
    }

    public function testSerialize()
    {
        $file = $this->filesystem->getFile('fixtures/images/1-top-left.jpg')->read();
        $expected = Image\Info::createFromString($file);
        /** @var Image\Info $actual */
        $actual = unserialize(serialize($expected));

        $this->assertInstanceOf(Image\Info::class, $actual);
        $this->assertEquals($expected->getDimensions(), $actual->getDimensions());
        $this->assertSame($expected->getType(), $actual->getType());
        $this->assertSame($expected->getBits(), $actual->getBits());
        $this->assertSame($expected->getChannels(), $actual->getChannels());
        $this->assertSame($expected->getMime(), $actual->getMime());
        $this->assertEquals($expected->getExif()->getData(), $actual->getExif()->getData());
        $this->assertSame($expected->isValid(), $actual->isValid());
    }

    public function testJsonSerialize()
    {
        $file = $this->filesystem->getFile('fixtures/images/1-top-left.jpg')->read();
        $expected = Image\Info::createFromString($file);
        $actual = Image\Info::createFromJson(json_decode(json_encode($expected), true));

        $this->assertEquals($expected->getDimensions(), $actual->getDimensions());
        $this->assertSame($expected->getType(), $actual->getType());
        $this->assertSame($expected->getBits(), $actual->getBits());
        $this->assertSame($expected->getChannels(), $actual->getChannels());
        $this->assertSame($expected->getMime(), $actual->getMime());
        $this->assertEquals($expected->getExif()->getData(), $actual->getExif()->getData());
        $this->assertSame($expected->isValid(), $actual->isValid());
    }

    public function testSvgFromString()
    {
        $file = $this->filesystem->getFile('fixtures/images/nut.svg')->read();
        $info = Image\Info::createFromString($file);

        $this->assertSame(1000, $info->getWidth());
        $this->assertSame(1000, $info->getHeight());
        $this->assertSame('image/svg+xml', $info->getMime());
        $this->assertTrue($info->isValid());
        $this->assertInstanceOf(Image\SvgType::class, $info->getType());
    }

    public function testSvgFromFile()
    {
        $info = Image\Info::createFromFile(__DIR__ . '/../../fixtures/images/nut.svg');

        $this->assertSame(1000, $info->getWidth());
        $this->assertSame(1000, $info->getHeight());
        $this->assertSame('image/svg+xml', $info->getMime());
        $this->assertTrue($info->isValid());
        $this->assertInstanceOf(Image\SvgType::class, $info->getType());
    }

    public function testSvgWithoutXmlDeclaration()
    {
        $file = $this->filesystem->getFile('fixtures/images/nut.svg');
        $data = $file->read();
        $data = substr($data, 39);
        $info = Image\Info::createFromString($data);

        $this->assertSame(1000, $info->getWidth());
        $this->assertSame(1000, $info->getHeight());
        $this->assertSame('image/svg+xml', $info->getMime());
        $this->assertTrue($info->isValid());
        $this->assertInstanceOf(Image\SvgType::class, $info->getType());
    }

    public function testReadExif()
    {
        $info = Image\Info::createFromFile(__DIR__ . '/../../fixtures2/empty.jpg');

        $m = new \ReflectionMethod(Image\Info::class,'readExif');
        $m->setAccessible(true);

        $exif = $m->invoke($info, __DIR__ . '/../../fixtures2/empty.jpg');
        $this->assertInstanceOf(Image\Exif::class, $exif);
    }
}
