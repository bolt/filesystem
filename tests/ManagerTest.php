<?php

namespace Bolt\Filesystem\Tests;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Finder;
use Bolt\Filesystem\Handler\Directory;
use Bolt\Filesystem\Handler\File;
use Bolt\Filesystem\Handler\HandlerInterface;
use Bolt\Filesystem\Handler\Image;
use Bolt\Filesystem\Manager;
use Bolt\Filesystem\PluginInterface;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Stream;
use PHPExif\Reader\Reader;
use Prophecy\Prophet;

class ManagerTest extends FilesystemTestCase
{
    /** @var FilesystemInterface */
    protected $filesystem;
    protected static $tmpDir = 'fixtures';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->filesystem = new Filesystem(new Local(__DIR__));
    }

    public function testMountFilesystem()
    {
        $manager = new Manager();
        $this->assertFalse($manager->hasFilesystem('test'));
        $manager->mountFilesystem('test', $this->filesystem);
        $this->assertTrue($manager->hasFilesystem('test'));
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\LogicException
     * @expectedExceptionMessageRegExp /does not have a handle method/
     */
    public function testMountPluggableFilesystem()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willExtend('stdClass');
        $prophecy->willImplement(PluginInterface::class);
        $prophecy->getMethod()->willReturn('dropbear');

        $manager = new Manager([], [$prophecy->reveal()]);
        $manager->mountFilesystem('test', $this->filesystem);
        $manager->dropbear();
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\InvalidArgumentException
     */
    public function testMountFilesystemInvaid()
    {
        $manager = new Manager();
        $manager->mountFilesystem(new \stdClass(), $this->filesystem);
    }

    public function testMountFilesystems()
    {
        $manager = new Manager();
        $this->assertFalse($manager->hasFilesystem('test'));
        $manager->mountFilesystems(['test' => $this->filesystem]);
        $this->assertTrue($manager->hasFilesystem('test'));
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\InvalidArgumentException
     */
    public function testMountFilesystemsInvaid()
    {
        $manager = new Manager();
        $manager->mountFilesystems(['test' => new \stdClass()]);
    }

    public function testGetFilesystem()
    {
        $manager = new Manager();
        $manager->mountFilesystems(['test' => $this->filesystem]);
        $this->assertInstanceOf(FilesystemInterface::class, $manager->getFilesystem('test'));
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\LogicException
     */
    public function testGetFilesystemInvaid()
    {
        $manager = new Manager();
        $manager->mountFilesystems(['test' => $this->filesystem]);
        $manager->getFilesystem('koala');
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\LogicException
     * @expectedExceptionMessageRegExp /does not have a handle method/
     */
    public function testAddPlugin()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willExtend('stdClass');
        $prophecy->willImplement(PluginInterface::class);
        $prophecy->getMethod()->willReturn('dropbear');

        $manager = new Manager(['test' => $this->filesystem]);
        $manager->addPlugins([$prophecy->reveal()]);
        $manager->dropbear();
    }

    public function testListContents()
    {
        $manager = new Manager(['test' => $this->filesystem]);
        $contents = $manager->listContents('test://');
        $this->assertInternalType('array', $contents);
        foreach ($contents as $content) {
            $this->assertInstanceOf(HandlerInterface::class, $content);
        }
    }

    public function testCopySameFilesystem()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/base.css'));
        $manager->copy('test://tests/fixtures/base.css', 'test://tests/temp/base.css');
        $this->assertTrue($manager->has('test://tests/temp/base.css'));
    }

    public function testCopyAcrossFilesystems()
    {
        $fs1 = new Filesystem(new Local($this->rootDir . '/tests/fixtures'));
        $fs2 = new Filesystem(new Local($this->rootDir . '/tests/temp'));
        $manager = new Manager(['fixtures' => $fs1, 'test' => $fs2]);

        $this->assertFalse($manager->has('test://base.css'));
        $manager->copy('fixtures://base.css', 'test://base.css');
        $this->assertTrue($manager->has('test://base.css'));
    }

    public function testRead()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertTrue($manager->has('test://tests/fixtures/base.css'));
        $data = $manager->read('test://tests/fixtures/base.css');
        $this->assertRegExp('/koala/', $data);
    }

    public function testReadStream()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertTrue($manager->has('test://tests/fixtures/base.css'));
        $data = $manager->readStream('test://tests/fixtures/base.css');
        $this->assertInstanceOf(Stream::class, $data);
    }

    public function testGetType()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertTrue($manager->has('test://tests/fixtures/images/1-top-left.jpg'));
        $type = $manager->getType('test://tests/fixtures/images/1-top-left.jpg');
        $this->assertSame('image', $type);
    }

    public function testGetSize()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertTrue($manager->has('test://tests/fixtures/images/1-top-left.jpg'));
        $size = $manager->getSize('test://tests/fixtures/images/1-top-left.jpg');
        $this->assertSame(5555, $size);
    }

    public function testGetMimeType()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertTrue($manager->has('test://tests/fixtures/images/1-top-left.jpg'));
        $size = $manager->getMimeType('test://tests/fixtures/images/1-top-left.jpg');
        $this->assertSame('image/jpeg', $size);
    }

    public function testGetTimeStamp()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertTrue($manager->has('test://tests/fixtures/images/1-top-left.jpg'));
        $timestamp = $manager->getTimestamp('test://tests/fixtures/images/1-top-left.jpg');
        $this->assertInternalType('integer', $timestamp);
    }

    public function testGetTimeStampCarbon()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertTrue($manager->has('test://tests/fixtures/images/1-top-left.jpg'));
        $carbon = $manager->getCarbon('test://tests/fixtures/images/1-top-left.jpg');
        $this->assertInstanceOf(Carbon::class, $carbon);
    }

    public function testGetVisibility()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertTrue($manager->has('test://tests/fixtures/images/1-top-left.jpg'));
        $visibility = $manager->getVisibility('test://tests/fixtures/images/1-top-left.jpg');
        $this->assertSame('public', $visibility);
    }

    public function testWrite()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->write('test://tests/temp/koala.txt', 'gum leaves');
        $this->assertSame('gum leaves', $manager->read('test://tests/temp/koala.txt'));
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\FileExistsException
     * @expectedExceptionMessage File already exists at path: tests/temp/koala.txt
     */
    public function testWriteExisting()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->write('test://tests/temp/koala.txt', 'gum leaves');
        // This will throw an exception as the line above creates the file
        $manager->write('test://tests/temp/koala.txt', 'gum leaves');
    }

    public function testWriteStream()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->writeStream('test://tests/temp/koala.txt', $manager->readStream('test://tests/fixtures/base.css'));
        $this->assertRegExp('/koala/', $manager->read('test://tests/temp/koala.txt'));
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\InvalidArgumentException
     */
    public function testWriteStreamInvalid()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->writeStream('test://tests/temp/koala.txt', 'gum leaves');
        $this->assertSame('gum leaves', $manager->read('test://tests/temp/koala.txt'));
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\FileExistsException
     * @expectedExceptionMessage File already exists at path: tests/temp/koala.txt
     */
    public function testWriteStreamExisting()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->writeStream('test://tests/temp/koala.txt', $manager->readStream('test://tests/fixtures/base.css'));
        // This will throw an exception as the line above creates the file
        $manager->writeStream('test://tests/temp/koala.txt', $manager->readStream('test://tests/fixtures/base.css'));
    }

    public function testUpdate()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->write('test://tests/temp/koala.txt', 'gum leaves');
        $this->assertSame('gum leaves', $manager->read('test://tests/temp/koala.txt'));

        $manager->update('test://tests/temp/koala.txt', 'drop bear');
        $this->assertSame('drop bear', $manager->read('test://tests/temp/koala.txt'));
    }

    public function testUpdateStream()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->write('test://tests/temp/koala.txt', 'gum leaves');
        $this->assertSame('gum leaves', $manager->read('test://tests/temp/koala.txt'));

        $manager->updateStream('test://tests/temp/koala.txt', $manager->readStream('test://tests/fixtures/base.css'));
        $this->assertRegExp('/koala/', $manager->read('test://tests/temp/koala.txt'));
    }

    public function testRename()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->write('test://tests/temp/koala.txt', 'gum leaves');
        $this->assertTrue($manager->has('test://tests/temp/koala.txt'));

        $this->assertFalse($manager->has('test://tests/temp/dropbear.txt'));
        $manager->rename('test://tests/temp/koala.txt', 'test://tests/temp/dropbear.txt');
        $this->assertTrue($manager->has('test://tests/temp/dropbear.txt'));
        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
    }

    public function testDelete()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $manager->write('test://tests/temp/koala.txt', 'gum leaves');
        $this->assertTrue($manager->has('test://tests/temp/koala.txt'));

        $manager->delete('test://tests/temp/koala.txt');
        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
    }

    public function testDeleteDir()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/gum-tree'));
        $manager->createDir('test://tests/temp/gum-tree');
        $this->assertTrue($manager->has('test://tests/temp/gum-tree'));

        $manager->deleteDir('test://tests/temp/gum-tree');
        $this->assertFalse($manager->has('test://tests/temp/gum-tree'));
    }

    public function testCopyDir()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/images'));
        $manager->copyDir('test://tests/fixtures/images', 'test://tests/temp/images');
        $this->assertTrue($manager->has('test://tests/temp/images'));
        $this->assertTrue($manager->has('test://tests/temp/images/1-top-left.jpg'));
    }

    public function testCopyDirAcrossFilesystems()
    {
        $fs1 = new Filesystem(new Local($this->rootDir . '/tests/fixtures'));
        $fs2 = new Filesystem(new Local($this->rootDir . '/tests/temp'));
        $manager = new Manager(['fixtures' => $fs1, 'test' => $fs2]);

        $this->assertFalse($manager->has('test://tests/temp/images'));
        $manager->copyDir('fixtures://images', 'test://images');
        $this->assertTrue($manager->has('test://images'));
        $this->assertTrue($manager->has('test://images/1-top-left.jpg'));
    }

    public function testMirror()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/images'));
        $manager->mirror('test://tests/fixtures/images', 'test://tests/temp/images');
        $this->assertTrue($manager->has('test://tests/temp/images'));
        $this->assertTrue($manager->has('test://tests/temp/images/1-top-left.jpg'));
    }

    public function testMirrorAcrossFilesystems()
    {
        $fs1 = new Filesystem(new Local($this->rootDir . '/tests/fixtures'));
        $fs2 = new Filesystem(new Local($this->rootDir . '/tests/temp'));
        $manager = new Manager(['fixtures' => $fs1, 'test' => $fs2]);

        $this->assertFalse($manager->has('test://tests/temp/images'));
        $manager->mirror('fixtures://images', 'test://images');
        $this->assertTrue($manager->has('test://images'));
        $this->assertTrue($manager->has('test://images/1-top-left.jpg'));
    }

    public function testMirrorDeleteExisting()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $manager->mirror('test://tests/fixtures/images', 'test://tests/temp/images');
        $manager->copy('test://tests/fixtures/base.css', 'test://tests/temp/images/base.css');
        $manager->copyDir('test://tests/fixtures/css', 'test://tests/temp/images/css');
        $this->assertTrue($manager->has('test://tests/temp/images/base.css'));
        $this->assertTrue($manager->has('test://tests/temp/images/css'));

        $manager->mirror('test://tests/fixtures/images', 'test://tests/temp/images');
        $this->assertFalse($manager->has('test://tests/temp/images/base.css'));
        $this->assertFalse($manager->has('test://tests/temp/images/css'));
    }

    public function testMirrorAcrossFilesystemsDeleteExisting()
    {
        $fs1 = new Filesystem(new Local($this->rootDir . '/tests/fixtures'));
        $fs2 = new Filesystem(new Local($this->rootDir . '/tests/temp'));
        $manager = new Manager(['fixtures' => $fs1, 'test' => $fs2]);

        $manager->mirror('fixtures://images', 'test://images');
        $manager->copy('fixtures://base.css', 'test://images/base.css');
        $manager->copyDir('fixtures://css', 'test://images/css');
        $this->assertTrue($manager->has('test://images/base.css'));

        $manager->mirror('fixtures://images', 'test://images');
        $this->assertFalse($manager->has('test://images/base.css'));
        $this->assertFalse($manager->has('test://images/css'));
    }

    public function testMirrorLeaveExisting()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $manager->mirror('test://tests/fixtures/images', 'test://tests/temp/images');
        $manager->copy('test://tests/fixtures/base.css', 'test://tests/temp/images/base.css');
        $manager->copyDir('test://tests/fixtures/css', 'test://tests/temp/images/css');
        $this->assertTrue($manager->has('test://tests/temp/images/base.css'));
        $this->assertTrue($manager->has('test://tests/temp/images/css'));

        $manager->mirror('test://tests/fixtures/images', 'test://tests/temp/images', ['delete' => false]);
        $this->assertTrue($manager->has('test://tests/temp/images/base.css'));
        $this->assertTrue($manager->has('test://tests/temp/images/css'));
    }

    public function testMirrorAcrossFilesystemsLeaveExisting()
    {
        $fs1 = new Filesystem(new Local($this->rootDir . '/tests/fixtures'));
        $fs2 = new Filesystem(new Local($this->rootDir . '/tests/temp'));
        $manager = new Manager(['fixtures' => $fs1, 'test' => $fs2]);

        $manager->mirror('fixtures://images', 'test://images');
        $manager->copy('fixtures://base.css', 'test://images/base.css');
        $manager->copyDir('fixtures://css', 'test://images/css');
        $this->assertTrue($manager->has('test://images/base.css'));

        $manager->mirror('fixtures://images', 'test://images', ['delete' => false]);
        $this->assertTrue($manager->has('test://images/base.css'));
        $this->assertTrue($manager->has('test://images/css'));
    }

    public function testSetVisibility()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $manager->copy('test://tests/fixtures/base.css', 'test://tests/temp/images/base.css');
        $this->assertTrue($manager->has('test://tests/temp/images/base.css'));

        $manager->setVisibility('test://tests/temp/images/base.css', 'private');
        $this->assertSame('private', $manager->getVisibility('test://tests/temp/images/base.css'));
    }

    public function testPutNew()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->put('test://tests/temp/koala.txt', 'drop bear');

        $this->assertSame('drop bear', $manager->read('test://tests/temp/koala.txt'));
    }

    public function testPutExisting()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $manager->copy('test://tests/fixtures/base.css', 'test://tests/temp/koala.txt');
        $this->assertTrue($manager->has('test://tests/temp/koala.txt'));
        $this->assertRegExp('/koala/', $manager->read('test://tests/temp/koala.txt'));

        $manager->put('test://tests/temp/koala.txt', 'drop bear');

        $this->assertSame('drop bear', $manager->read('test://tests/temp/koala.txt'));
    }

    public function testPutStreamNew()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->putStream('test://tests/temp/koala.txt', $manager->readStream('test://tests/fixtures/css/style.css'));

        $this->assertRegExp('/color: white/', $manager->read('test://tests/temp/koala.txt'));
    }

    public function testPutStreamExisting()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $manager->copy('test://tests/fixtures/base.css', 'test://tests/temp/koala.txt');
        $this->assertTrue($manager->has('test://tests/temp/koala.txt'));
        $this->assertRegExp('/color: grey/', $manager->read('test://tests/temp/koala.txt'));

        $manager->putStream('test://tests/temp/koala.txt', $manager->readStream('test://tests/fixtures/css/style.css'));

        $this->assertRegExp('/color: white/', $manager->read('test://tests/temp/koala.txt'));
    }

    public function testReadAndDelete()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
        $manager->copy('test://tests/fixtures/base.css', 'test://tests/temp/koala.txt');

        $this->assertRegExp('/color: grey/', $manager->readAndDelete('test://tests/temp/koala.txt'));
        $this->assertFalse($manager->has('test://tests/temp/koala.txt'));
    }

    public function testGet()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertInstanceOf(File::class, $manager->get('test://tests/fixtures/images/1-top-left.jpg'));
        $this->assertInstanceOf(Directory::class, $manager->get('test://tests/fixtures/images'));
    }

    public function testGetFile()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertInstanceOf(File::class, $manager->getFile('test://tests/fixtures/images/1-top-left.jpg'));
    }

    public function testGetDir()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertInstanceOf(Directory::class, $manager->getDir('test://tests/fixtures/images'));
    }

    public function testGetImage()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertInstanceOf(Image::class, $manager->getImage('test://tests/fixtures/images/1-top-left.jpg'));
    }

    public function testGetImageInfo()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $info = $manager->getImageInfo('test://tests/fixtures/images/1-top-left.jpg');
        $this->assertInstanceOf(Image\Info::class, $info);
        $this->assertSame('image/jpeg', $info->getMime());
        $this->assertInstanceOf(Image\Exif::class, $info->getExif());
        $this->assertInstanceOf(Image\Dimensions::class, $info->getDimensions());
        $this->assertInstanceOf(Image\CoreType::class, $info->getType());
    }

    public function testFind()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $this->assertInstanceOf(Finder::class, $manager->find());
    }

    public function testIncludeFile()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);

        $manager->put('test://tests/temp/nuke-it.php', '<?php return "include worked";');
        $include = $manager->includeFile('test://tests/temp/nuke-it.php');

        $this->assertSame('include worked', $include);
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\InvalidArgumentException
     * @expectedExceptionMessage Path should be a string, stdClass provided.
     */
    public function testParsePathNotString()
    {
        $filesystem = new Filesystem(new Local($this->rootDir));
        $manager = new Manager(['test' => $filesystem]);
        $manager->get(new \stdClass());
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\InvalidArgumentException
     * @expectedExceptionMessage No mount point detected in path:
     */
    public function testParsePathInvalidMount()
    {
        $manager = new Manager();
        $manager->get('://');
    }
}
