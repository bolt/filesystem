<?php

namespace Bolt\Filesystem\Tests\Adapter;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\Tests\FilesystemTestCase;
use League\Flysystem\Config;

/**
 * Tests for Bolt\Filesystem\File
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class LocalTest extends FilesystemTestCase
{
    /** @var FilesystemInterface */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->filesystem = new Filesystem(new Local($this->rootDir . '/tests'));
    }

    public function testConstruct()
    {
        if (posix_getuid() === 0) {
            $this->fail('Do not run as root user');
        }

        $local = new Local($this->tempDir);
        $this->assertInstanceOf('Bolt\Filesystem\Adapter\Local', $local);

        $this->setExpectedException('Bolt\Filesystem\Exception\DirectoryCreationException', 'Failed to create directory');
        $local = new Local('/bad');
    }

    public function testUpdate()
    {
        $this->filesystem->get('fixtures/base.css')->copy('temp/koala.css');
        $local = new Local($this->tempDir);
        $config = new Config();

        $update = $local->update('koala.css', '.drop-bear {}', $config);
        $this->assertSame('koala.css', $update['path']);
        $this->assertSame('.drop-bear {}', $update['contents']);
        $this->assertSame('text/css', $update['mimetype']);

        $update = $local->update('koala.css.typo', '.drop-bear {}', $config);
        $this->assertFalse($update);
    }

    public function testDelete()
    {
        $this->filesystem->get('fixtures/base.css')->copy('temp/koala.css');
        $local = new Local($this->tempDir);
        $delete = $local->delete('koala.css');
        $this->assertTrue($delete);

        $delete = $local->delete('koala.css.typo');
        $this->assertFalse($delete);
    }

    public function testCreateDir()
    {
        $local = new Local($this->tempDir);
        $config = new Config();

        $create = $local->createDir('horse-with-no-name', $config);
        $this->assertSame('horse-with-no-name', $create['path']);
        $this->assertSame('dir', $create['type']);

        $this->filesystem->get('fixtures/base.css')->copy('temp/horse-with-no-name/koala.css');
        $create = $local->createDir('horse-with-no-name/koala.css', $config);
        $this->assertFalse($create);
    }

    public function testDeleteDir()
    {
        $local = new Local($this->tempDir);
        $config = new Config();

        $local->createDir('horse-with-no-name', $config);
        $delete = $local->deleteDir('horse-with-no-name');

        $this->assertTrue($delete);
        $this->assertFalse($local->has('horse-with-no-name'));

        $delete = $local->deleteDir('horse-with-no-name');
        $this->assertFalse($delete);
    }
}
