<?php

namespace Bolt\Filesystem\Tests;

use Bolt\Filesystem\File;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\Local;
use Bolt\Filesystem\StreamWrapper;
use Bolt\Filesystem\Tests\Mock\ArrayCacheMock;
use Doctrine\Common\Cache\VoidCache;

/**
 * @coversDefaultClass \Bolt\Filesystem\StreamWrapper
 */
class StreamWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $root;
    /** @var Filesystem */
    protected $fs;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->root = __DIR__ . '/..';
        $this->fs = new Filesystem(new Local($this->root));
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        StreamWrapper::unregister('test-fs');

        $dir = __DIR__ . '/tmp';
        if (!file_exists($dir)) {
            return;
        }
        $it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file => $info) {
            if (is_dir($file)) {
                @rmdir($file);
            } else {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }

    public function testRegister()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->assertContains('test-fs', stream_get_wrappers(), 'wrapper was not registered');
        $options = stream_context_get_options(stream_context_get_default());
        $this->assertSame($this->fs, $options['test-fs']['filesystem'], 'filesystem was not set in stream context');
        $this->assertInstanceOf(
            '\Doctrine\Common\Cache\ArrayCache',
            $options['test-fs']['cache'],
            'Default cache instance was not set in stream context'
        );

        $myCache = new VoidCache(); // Don't actually do this
        StreamWrapper::register($this->fs, 'test-fs', $myCache); // This also tests re-registering

        $options = stream_context_get_options(stream_context_get_default());
        $this->assertSame(
            $myCache,
            $options['test-fs']['cache'],
            'Cache instance given to register() was not set in stream context'
        );
    }

    public function testUnregister()
    {
        StreamWrapper::register($this->fs, 'test-fs');
        StreamWrapper::unregister('test-fs');

        $this->assertNotContains('test-fs', stream_get_wrappers(), 'wrapper was not unregistered');
        $options = stream_context_get_options(stream_context_get_default());
        foreach ($options['test-fs'] as $key => $value) {
            $this->assertNull($value, "protocol option '$key' was not set to null'");
        }
    }

    public function testGetHandler()
    {
        StreamWrapper::register($this->fs, 'test-fs');
        $handler = StreamWrapper::getHandler('test-fs://composer.json');
        $this->assertInstanceOf('\Bolt\Filesystem\File', $handler);
        $this->assertSame('composer.json', $handler->getFilename(), 'handler points to wrong file');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetHandlerInvalidPath()
    {
        StreamWrapper::getHandler('foobar');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Filesystem does not exist for that protocol
     */
    public function testGetHandlerNotRegistered()
    {
        StreamWrapper::getHandler('foo://bar');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Filesystem does not exist for that protocol
     */
    public function testGetHandlerNonExistentFilesystem()
    {
        StreamWrapper::register($this->fs, 'test-fs');
        $options = stream_context_get_options(stream_context_get_default());
        $options['test-fs']['filesystem'] = null;
        stream_context_set_default($options);

        StreamWrapper::getHandler('test-fs://what-are-you-even-doing');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Filesystem needs to be an instance of Bolt\Filesystem\FilesystemInterface
     */
    public function testGetHandlerUnexpectedFilesystem()
    {
        StreamWrapper::register($this->fs, 'test-fs');
        $options = stream_context_get_options(stream_context_get_default());
        $options['test-fs']['filesystem'] = 'some people just want to watch the world burn';
        stream_context_set_default($options);

        StreamWrapper::getHandler('test-fs://why-what-no');
    }

    /**
     * @expectedException \Bolt\Filesystem\Exception\FileNotFoundException
     */
    public function testGetHandlerNonExistentPath()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        StreamWrapper::getHandler('test-fs://does-not-exist');
    }

    public function testGetHandlerNonExistentPathWithHandlerGiven()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $handler = StreamWrapper::getHandler('test-fs://does-not-exist', new File());
        $this->assertInstanceOf('\Bolt\Filesystem\File', $handler);
    }

    public function testUrlStatFile()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->catchWarnings(
            function (\ArrayObject $warnings) {
                $nativePath = $this->root . '/composer.json';
                $streamPath = 'test-fs://composer.json';

                $this->assertSame(filesize($nativePath), filesize($streamPath), 'filesize');
                $this->assertSame(filemtime($nativePath), filemtime($streamPath), 'filemtime');
                $this->assertSame(filetype($nativePath), filetype($streamPath), 'filetype');
                $this->assertTrue(is_file($streamPath), 'is_file');
                $this->assertFalse(is_dir($streamPath), 'is_dir');
                $this->assertFalse(is_link($streamPath), 'is_link');
                $this->assertTrue(is_writable($streamPath), 'is_writable');
                $this->assertTrue(is_readable($streamPath), 'is_readable');
                $this->assertTrue(file_exists($streamPath), 'file_exists');

                $this->assertEmpty($warnings, 'url_stat should not trigger warnings for a valid file');
            }
        );
    }

    public function testUrlStatDirectory()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->catchWarnings(
            function (\ArrayObject $warnings) {
                $nativePath = $this->root . '/src';
                $streamPath = 'test-fs://src';

                $this->assertSame(0, filesize($streamPath), 'filesize');
                $this->assertSame(filemtime($nativePath), filemtime($streamPath), 'filemtime');
                $this->assertSame(filetype($nativePath), filetype($streamPath), 'filetype');
                $this->assertFalse(is_file($streamPath), 'is_file');
                $this->assertTrue(is_dir($streamPath), 'is_dir');
                $this->assertFalse(is_link($streamPath), 'is_link');
                $this->assertTrue(is_writable($streamPath), 'is_writable');
                $this->assertTrue(is_readable($streamPath), 'is_readable');
                $this->assertTrue(file_exists($streamPath), 'file_exists');

                $this->assertEmpty($warnings, 'url_stat should not trigger warnings for a valid directory');
            }
        );
    }

    public function testUrlStatNonExistent()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->catchWarnings(
            function (\ArrayObject $warnings) {
                $streamPath = 'test-fs://wut';

                $methods = ['filesize', 'filemtime', 'filetype'];
                foreach ($methods as $method) {
                    $this->assertFalse($method($streamPath), $method);
                    $this->assertContainsSubstring($method, $warnings);
                    $warnings->exchangeArray([]); // empty it
                }

                $methods = ['is_file', 'is_dir', 'is_link', 'is_writable', 'is_readable', 'file_exists'];
                foreach ($methods as $method) {
                    $this->assertFalse($method($streamPath), $method);
                }
                $this->assertEmpty($warnings, 'url_stat should not trigger warnings for is_* methods');
            }
        );
    }

    private function assertContainsSubstring($expected, $list)
    {
        foreach ($list as $item) {
            if (strpos($item, $expected) !== false) {
                return;
            }
        }

        $this->fail('List did not contain a string containing: ' . $expected);
    }

    public function testUrlStatCaching()
    {
        $cache = new ArrayCacheMock();
        StreamWrapper::register($this->fs, 'test-fs', $cache);

        $path = 'test-fs://composer.json';
        $this->assertTrue(is_file($path));
        $this->assertTrue($cache->contains($path), 'url_stat did not cache result');

        clearstatcache(true, $path);
        is_file($path);
        $this->assertSame(1, $cache->saveInvoked, 'url_stat did not use cached value');
    }

    /**
     * @covers ::dir_opendir
     * @covers ::dir_readdir
     * @covers ::dir_rewinddir
     * @covers ::dir_closedir
     */
    public function testDirectoryIteration()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $it = new \RecursiveDirectoryIterator('test-fs://tests/files');
        $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::SELF_FIRST);
        $files = array_keys(iterator_to_array($it));

        $expected = [
            'test-fs://tests/files/dir_a',
            'test-fs://tests/files/dir_a/bar.txt',
            'test-fs://tests/files/dir_a/foo.txt',
            'test-fs://tests/files/dir_b',
            'test-fs://tests/files/dir_b/dir_c',
            'test-fs://tests/files/dir_b/dir_c/nested.txt',
            'test-fs://tests/files/dir_b/hello.txt',
            'test-fs://tests/files/dir_b/world.txt',
        ];

        $this->assertEquals($expected, $files);
    }

    /**
     * @covers ::dir_opendir
     */
    public function testOpenDirFail()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->catchWarnings(
            function (\ArrayObject $warnings) {
                $resource = opendir('test-fs://nonexistent');
                $this->assertFalse($resource);
                $this->assertContainsSubstring('File not found at path: nonexistent', $warnings);
            }
        );
    }

    /**
     * @covers ::stream_open
     * @covers ::stream_read
     * @covers ::stream_eof
     * @covers ::stream_flush
     * @covers ::stream_close
     */
    public function testStreamReading()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->catchWarnings(
            function (\ArrayObject $warnings) {
                $res = fopen('test-fs://tests/files/dir_b/hello.txt', 'r');

                $this->assertSame('hello', trim(stream_get_contents($res)));

                $this->assertEmpty($warnings, 'fopen should not trigger warnings for a valid file');
            }
        );
    }

    /**
     * @covers ::stream_open
     * @covers ::stream_write
     * @covers ::stream_tell
     * @covers ::stream_read
     * @covers ::stream_flush
     * @covers ::stream_close
     */
    public function testStreamWriting()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->catchWarnings(
            function (\ArrayObject $warnings) {
                $path = 'tests/tmp/test.txt';
                $res = fopen('test-fs://' . $path, 'w');

                $this->assertSame(11, fwrite($res, 'hello world'));

                $this->assertSame(11, ftell($res));

                $this->assertSame('', fread($res, 11), 'Should not be able to read from a writable-only stream');

                $this->assertTrue(fclose($res));

                $this->assertEmpty($warnings, 'Writing to stream should not trigger warnings for a valid file');

                $this->assertSame('hello world', $this->fs->read($path));
            }
        );
    }

    /**
     * @covers ::stream_seek
     * @covers ::stream_tell
     */
    public function testStreamSeeking()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->catchWarnings(
            function (\ArrayObject $warnings) {
                $res = fopen('test-fs://tests/Mock/ArrayCacheMock.php', 'r');

                $expected = fread($res, 1024);
                $this->assertTrue(rewind($res));
                $actual = fread($res, 1024);

                $this->assertSame($expected, $actual);

                $this->assertEmpty($warnings, 'Stream seeking should not trigger warnings for a seekable stream');
            }
        );
    }

    /**
     * @covers ::stream_stat
     */
    public function testStreamStatReading()
    {
        StreamWrapper::register($this->fs, 'test-fs');
        $res = fopen('test-fs://tests/files/dir_b/hello.txt', 'r');

        $stats = fstat($res);

        $this->assertSame(0100444, $stats['mode']);
        $this->assertSame(filesize(__DIR__ . '/files/dir_b/hello.txt'), $stats['size']);
        $this->assertSame(filemtime(__DIR__ . '/files/dir_b/hello.txt'), $stats['mtime']);
    }

    /**
     * @covers ::stream_stat
     */
    public function testStreamStatWriting()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $res = fopen('test-fs://tests/tmp/foo.txt', 'w');
        fwrite($res, 'herp derp');

        $stats = fstat($res);

        $this->assertSame(0100644, $stats['mode']);
        $this->assertSame(9, $stats['size']);
        $this->assertSame(0, $stats['mtime']);
    }

    /**
     * @covers ::stream_open
     */
    public function testStreamOpenWritingOnlyIfNotExists()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        $this->catchWarnings(
            function (\ArrayObject $warnings) {
                $this->assertFalse(fopen('test-fs://tests/files/dir_b/hello.txt', 'x'));
                $this->assertContainsSubstring('tests/files/dir_b/hello.txt already exists.', $warnings);

                $warnings->exchangeArray([]); // empty it

                $this->assertFalse(fopen('test-fs://tests/files/dir_b/hello.txt', 'x+'));
                $this->assertContainsSubstring('Simultaneous reading and writing is not supported.', $warnings);
            }
        );
    }

    /**
     * @covers ::stream_open
     */
    public function testStreamOpenUnsupportedModes()
    {
        StreamWrapper::register($this->fs, 'test-fs');

        foreach (['r+', 'w+', 'a', 'a+', 'x+', 'c', 'c+'] as $mode) {
            $this->assertStreamModeNotSupported('test-fs://tests/files/dir_b/hello.txt', $mode);
        }
    }

    /**
     * @param string $path
     * @param string $mode
     */
    private function assertStreamModeNotSupported($path, $mode)
    {
        $this->catchWarnings(
            function (\ArrayObject $warnings) use ($path, $mode) {
                $this->assertFalse(fopen($path, $mode));
                if (strpos($mode, '+') !== false) {
                    $this->assertContainsSubstring('Simultaneous reading and writing is not supported.', $warnings);
                } else {
                    $this->assertContainsSubstring("Mode not supported: {$mode}. Use r, w, x.", $warnings);
                }
            }
        );
    }

    /**
     * Stores all warnings in an ArrayObject passed to the callable.
     *
     * @param callable $func
     *
     * @throws \Exception
     */
    private function catchWarnings(callable $func)
    {
        $warnings = new \ArrayObject();
        set_error_handler(
            function ($errno, $errstr) use ($warnings) {
                $warnings[] = $errstr;
            },
            E_USER_WARNING | E_WARNING
        );

        try {
            $func($warnings);
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }

        restore_error_handler();
    }
}
