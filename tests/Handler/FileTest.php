<?php

namespace Bolt\Filesystem\Tests\Handler;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Handler\File;
use Bolt\Filesystem\Tests\FilesystemTestCase;

/**
 * Tests for Bolt\Filesystem\Handler\File
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class FileTest extends FilesystemTestCase
{
    /** @var FilesystemInterface */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->filesystem = new Filesystem(new Local(__DIR__ . '/../'));
    }

    public function testConstruct()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertInstanceOf('Bolt\Filesystem\Handler\File', $file);

        $filesystem = new Filesystem(new Local(__DIR__));
        $file = new File($filesystem);
        $this->assertInstanceOf('Bolt\Filesystem\Handler\File', $file);
    }

    public function testSetFilesystem()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $filesystem = new Filesystem(new Local(__DIR__));
        $file->setFilesystem($filesystem);
        $this->assertInstanceOf('Bolt\Filesystem\Filesystem', $file->getFilesystem());
    }

    public function testGetMimeType()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame('image/jpeg', $file->getMimeType(false));
    }

    public function testGetVisibility()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame('public', $file->getVisibility(false));
    }

    public function testGetType()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame('image', $file->getType(false));
    }

    public function testGetSize()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame(7023, $file->getSize(false));
    }

    public function testGetSizeFormatted()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame('6.86 KiB', $file->getSizeFormatted());
        $this->assertSame('7.0 KB', $file->getSizeFormatted(true));
    }

    public function testReadStream()
    {
        $file = new File($this->filesystem, 'fixtures/base.css');
        $stream = $file->readStream();
        $this->assertInstanceOf('Psr\Http\Message\StreamInterface', $stream);
        $this->assertRegExp('/koala/', (string) $stream);
        $this->assertRegExp('/color: grey;/', (string) $stream);
        $this->assertRegExp('/width: 100%/', (string) $stream);
    }

    public function testWrite()
    {
        $text = 'Attack of the drop bear';
        $file = new File($this->filesystem, 'temp/dropbear.log');
        $file->write($text);

        $newFile = new File($this->filesystem, 'temp/dropbear.log');
        $this->assertSame('Attack of the drop bear', $newFile->read());

        $this->setExpectedException('Bolt\Filesystem\Exception\FileExistsException', 'File already exists at path: temp/dropbear.log');
        $newFile->write('anything');
    }

    public function testWriteStream()
    {
        $file = new File($this->filesystem, 'fixtures/base.css');
        $stream = $file->readStream();

        $newFile = new File($this->filesystem, 'temp/base.css');
        $newFile->writeStream($stream);

        $this->assertSame($file->read(), $newFile->read());

        $this->setExpectedException('Bolt\Filesystem\Exception\FileExistsException', 'File already exists at path: temp/base.css');
        $newFile->writeStream($stream);
    }

    public function testUpdate()
    {
        $path = 'temp/Spiderbait.txt';

        $file = new File($this->filesystem, $path);

        $file->write(null);
        $file->update('Buy me a pony');

        $newFile = new File($this->filesystem, $path);
        $this->assertSame('Buy me a pony', $newFile->read());

        $file = new File($this->filesystem, $path);
        $file->update('Calypso');

        $newFile = new File($this->filesystem, $path);
        $this->assertSame('Calypso', $newFile->read());
    }

    public function testUpdateStream()
    {
        $file = new File($this->filesystem, 'fixtures/base.css');
        $stream = $file->readStream();

        $newFile = new File($this->filesystem, 'temp/koala.css');
        $newFile->write(null);
        $newFile->updateStream($stream);

        $this->assertSame($file->read(), $newFile->read());
    }

    public function testPut()
    {
        $path = 'temp/SilversunPickups.txt';

        $file = new File($this->filesystem, $path);
        $this->assertFalse($file->exists());
        $file->write(null);
        $file->put('Nightlight');

        $newFile = new File($this->filesystem, $path);
        $this->assertSame('Nightlight', $newFile->read());

        $file = new File($this->filesystem, $path);
        $this->assertTrue($file->exists());
        $file->put("It's nice to know you work alone");

        $newFile = new File($this->filesystem, $path);
        $this->assertSame("It's nice to know you work alone", $newFile->read());
    }

    public function testPutStream()
    {
        $file = new File($this->filesystem, 'fixtures/base.css');
        $stream = $file->readStream();

        $newFile = new File($this->filesystem, 'temp/koala.css');
        $this->assertFalse($newFile->exists());
        $newFile->putStream($stream);

        $this->assertSame($file->read(), $newFile->read());
    }

    public function testRename()
    {
        $pathOld = 'temp/the-file-formerly-known-as.txt';
        $pathNew = 'temp/the-file.txt';

        $file = new File($this->filesystem, $pathOld);
        $this->assertFalse($file->exists());
        $file->write('Writing tests is so much fun… everyone should do it!');
        $file->rename($pathNew);

        $newFile = new File($this->filesystem, $pathNew);
        $this->assertSame('Writing tests is so much fun… everyone should do it!', $newFile->read());
    }

    public function testCopy()
    {
        $file = new File($this->filesystem, 'fixtures/base.css');
        $file->copy('temp/drop-the-base.css');

        $newFile = new File($this->filesystem, 'temp/drop-the-base.css');
        $this->assertSame($file->read(), $newFile->read());
    }

    public function testDelete()
    {
        $file = new File($this->filesystem, 'fixtures/base.css');
        $file->copy('temp/drop-the-base.css');

        $newFile = new File($this->filesystem, 'temp/drop-the-base.css');
        $this->assertSame($file->read(), $newFile->read());
        $newFile->delete();
        $this->assertFalse($newFile->exists());

        $newNewFile = new File($this->filesystem, 'temp/drop-the-base.css');
        $this->assertFalse($newNewFile->exists());
    }
}
