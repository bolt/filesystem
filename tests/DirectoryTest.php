<?php

namespace Bolt\Filesystem\Tests;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Directory;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\Tests\FilesystemTestCase;
use League\Flysystem;

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
        $this->filesystem = new Filesystem(new Local(__DIR__));
    }

    public function testConstruct()
    {
        $dir = new Directory($this->filesystem);
        $this->assertInstanceOf('Bolt\Filesystem\Directory', $dir);

        $filesystem = new Flysystem\Filesystem(new Local(__DIR__));
        $dir = new Directory($filesystem);
        $this->assertInstanceOf('Bolt\Filesystem\Directory', $dir);
    }

    public function testCast()
    {
        $dir = new Directory($this->filesystem);
        $filesystem = new Flysystem\Filesystem(new Local(__DIR__));
        $dir = Directory::cast(new Flysystem\Directory($filesystem));
        $this->assertInstanceOf('Bolt\Filesystem\Directory', $dir);
    }

    public function testSetFilesystem()
    {
        $dir = new Directory($this->filesystem);
        $filesystem = new Flysystem\Filesystem(new Local(__DIR__));
        $dir->setFilesystem($filesystem);
        $this->assertInstanceOf('Bolt\Filesystem\Filesystem', $dir->getFilesystem());
    }

    public function testGet()
    {
        $dir = new Directory($this->filesystem);
        $this->assertInstanceOf('Bolt\Filesystem\File', $dir->get('fixtures/base.css'));
    }

    public function testGetContents()
    {
        $dir = new Directory($this->filesystem);
        $content = $dir->getContents();
        $this->assertInstanceOf('League\Flysystem\Handler', $content[0]);
    }

    public function testExists()
    {
        $dir = new Directory($this->filesystem);
        $this->assertTrue($dir->exists());
    }
}
