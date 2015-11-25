<?php

namespace Bolt\Filesystem\Tests\Image;

use Bolt\Filesystem\Image\Type;
use PHPExif;

/**
 * Tests for Bolt\Filesystem\Image\Type
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $type = new Type(IMAGETYPE_JPEG);
        $this->assertInstanceOf('Bolt\Filesystem\Image\Type', $type);

        $this->setExpectedException('\InvalidArgumentException', 'Given type is not an IMAGETYPE_* constant');
        new Type(42);
    }

    public function testToInt()
    {
        $type = new Type(IMAGETYPE_JPEG);
        $this->assertSame(2, $type->toInt());
    }

    public function testToMimeType()
    {
        $type = new Type(IMAGETYPE_JPEG);
        $this->assertSame('image/jpeg', $type->toMimeType());
    }

    public function testToExtension()
    {
        $type = new Type(IMAGETYPE_JPEG);
        $this->assertSame('.jpeg', $type->toExtension());
    }

    public function testToString()
    {
        $type = new Type(IMAGETYPE_JPEG);
        $this->assertSame('jpeg', $type->toString());
        $this->assertSame('jpeg', (string) $type);
    }

    public function testGetTypes()
    {
        $types = Type::getTypes();
        $this->assertTrue(in_array('jpeg', $types));
    }

    public function testGetTypeExtensions()
    {
        $extensions = Type::getTypeExtensions();
        $this->assertTrue(in_array('jpeg', $extensions));
    }
}
