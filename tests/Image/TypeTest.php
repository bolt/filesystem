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
    public function testGetById()
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        $this->assertInstanceOf('Bolt\Filesystem\Image\Type', $type);

        $type2 = Type::getById(IMAGETYPE_JPEG);
        $this->assertSame($type, $type2);

        $this->setExpectedException('\InvalidArgumentException', 'Given type is not an IMAGETYPE_* constant');
        Type::getById(42);
    }

    public function testToId()
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        $this->assertSame(2, $type->toId());
    }

    public function testToMimeType()
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        $this->assertSame('image/jpeg', $type->toMimeType());
    }

    public function testToExtension()
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        $this->assertSame('.jpeg', $type->toExtension(true));
        $this->assertSame('jpeg', $type->toExtension(false));
    }

    public function testToString()
    {
        $type = Type::getById(IMAGETYPE_JPEG);
        $this->assertSame('JPEG', $type->toString());
        $this->assertSame('JPEG', (string) $type);
    }

    public function testGetTypes()
    {
        $types = Type::getTypes();
        $this->assertInstanceOf('Bolt\Filesystem\Image\Type', $types[0]);
    }

    public function testGetMimeTypes()
    {
        $mimeTypes = Type::getMimeTypes();
        $this->assertContains('image/jpeg', $mimeTypes);
    }

    public function testGetExtensions()
    {
        $extensions = Type::getExtensions();
        $this->assertContains('jpeg', $extensions);
        $this->assertContains('jpg', $extensions);
    }
}
