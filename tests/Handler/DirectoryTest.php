<?php

namespace Bolt\Filesystem\Tests\Handler;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Handler\Directory;
use Bolt\Filesystem\Handler\File;
use Bolt\Filesystem\Handler\HandlerInterface;
use Bolt\Filesystem\Tests\FilesystemTestCase;

/**
 * Tests for Bolt\Filesystem\Directory
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DirectoryTest extends FilesystemTestCase
{
    /** @var FilesystemInterface */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->filesystem = new Filesystem(new Local(__DIR__ . '/../'));
    }

    public function testConstruct()
    {
        $dir = new Directory($this->filesystem);
        $this->assertInstanceOf(Directory::class, $dir);

        $filesystem = new Filesystem(new Local(__DIR__));
        $dir = new Directory($filesystem);
        $this->assertInstanceOf(Directory::class, $dir);
    }

    public function testSetFilesystem()
    {
        $dir = new Directory($this->filesystem);
        $filesystem = new Filesystem(new Local(__DIR__));
        $dir->setFilesystem($filesystem);
        $this->assertInstanceOf(Filesystem::class, $dir->getFilesystem());
    }

    public function testGet()
    {
        $dir = new Directory($this->filesystem);
        $this->assertInstanceOf(File::class, $dir->get('fixtures/base.css'));
    }

    public function testGetContents()
    {
        $dir = new Directory($this->filesystem);
        $content = $dir->getContents();
        $this->assertInstanceOf(HandlerInterface::class, $content[0]);
    }

    public function testExists()
    {
        $dir = new Directory($this->filesystem);
        $this->assertTrue($dir->exists());
    }
}
