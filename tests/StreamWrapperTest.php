<?php

namespace Bolt\Filesystem\Tests;

use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\Local;
use Bolt\Filesystem\StreamWrapper;
use Doctrine\Common\Cache\VoidCache;

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
}
