<?php

namespace Bolt\Filesystem\Tests;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\File;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\Tests\FilesystemTestCase;
use League\Flysystem;

/**
 * Tests for Bolt\Filesystem\File
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
        $this->filesystem = new Filesystem(new Local(__DIR__));
    }

    public function testConstruct()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertInstanceOf('Bolt\Filesystem\File', $file);

        $filesystem = new Flysystem\Filesystem(new Local(__DIR__));
        $file = new File($filesystem);
        $this->assertInstanceOf('Bolt\Filesystem\File', $file);
    }

    public function testCast()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $filesystem = new Flysystem\Filesystem(new Local(__DIR__));
        $file = File::cast(new Flysystem\File($filesystem));
        $this->assertInstanceOf('Bolt\Filesystem\File', $file);
    }

    public function testSetFilesystem()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $filesystem = new Flysystem\Filesystem(new Local(__DIR__));
        $file->setFilesystem($filesystem);
        $this->assertInstanceOf('Bolt\Filesystem\Filesystem', $file->getFilesystem());
    }

    public function testGetMimetype()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame('image/jpeg', $file->getMimetype(false));
    }

    public function testGetVisibility()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame('public', $file->getVisibility(false));
    }

    public function testGetMetadata()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $meta = $file->getMetadata(false);
        $this->assertSame('image', $meta['type']);
        $this->assertSame('fixtures/images/2-top-right.jpg', $meta['path']);
        $this->assertSame(7023, $meta['size']);
    }

    public function testGetSize()
    {
        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame(7023, $file->getSize(false));
    }

    public function testGetSizeFormatted()
    {
        $file2 = File::createFromListingEntry($this->filesystem, ['path' => 'fixtures/images/2-top-right.jpg', 'size' => 123]);
        $this->assertSame('123 B', $file2->getSizeFormatted(true));

        $file = new File($this->filesystem, 'fixtures/images/2-top-right.jpg');
        $this->assertSame('6.86 KiB', $file->getSizeFormatted(false));

        $file2 = File::createFromListingEntry($this->filesystem, ['path' => 'fixtures/images/2-top-right.jpg', 'size' => 12345678]);
        $this->assertSame('11.77 MiB', $file2->getSizeFormatted(true));
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
    }

    public function testWriteStream()
    {
        $file = new File($this->filesystem, 'fixtures/base.css');
        $stream = $file->readStream();

        $newFile = new File($this->filesystem, 'temp/base.css');
        $newFile->writeStream($stream);

        $this->assertSame($file->read(), $newFile->read());
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
