<?php

namespace Bolt\Filesystem\Tests\Iterator;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Iterator\GlobIterator;
use Symfony\Component\Filesystem as Symfony;
use Webmozart\Glob\Glob;

class GlobIteratorTest extends IteratorTestCase
{
    /** @var FilesystemInterface */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->filesystem = new Filesystem(new Local($this->tempDir));

        (new Symfony\Filesystem())->mirror($this->rootDir . '/tests/fixtures', $this->tempDir);
    }

    public function testIterate()
    {
        $iterator = new GlobIterator($this->filesystem, '/*.css');

        $this->assertIterator(
            [
                'base.css',
            ],
            $iterator
        );
    }

    public function testIterateEscaped()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('A "*" in filenames is not supported on Windows.');

            return;
        }

        touch($this->tempDir . '/css/style*.css');

        $iterator = new GlobIterator($this->filesystem, '/css/style\\*.css');

        $this->assertIterator(
            [
                'css/style*.css',
            ],
            $iterator
        );
    }

    public function testIterateSpecialChars()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('A "*" in filenames is not supported on Windows.');

            return;
        }

        touch($this->tempDir . '/css/style*.css');

        $iterator = new GlobIterator($this->filesystem, '/css/style*.css');

        $this->assertIterator(
            [
                'css/style*.css',
                'css/style.css',
            ],
            $iterator
        );
    }

    public function testIterateDoubleWildcard()
    {
        $iterator = new GlobIterator($this->filesystem, '/**/*.css');

        $this->assertIterator(
            [
                'base.css',
                'css/old/old_style.css',
                'css/reset.css',
                'css/style.css',
            ],
            $iterator
        );
    }

    public function testIterateSingleDirectory()
    {
        $iterator = new GlobIterator($this->filesystem, '/css');

        $this->assertIterator(
            [
                'css',
            ],
            $iterator
        );
    }

    public function testIterateSingleFile()
    {
        $iterator = new GlobIterator($this->filesystem, '/css/style.css');

        $this->assertIterator(
            [
                'css/style.css',
            ],
            $iterator
        );
    }

    public function testIterateSingleFileInDirectoryWithUnreadableFiles()
    {
        $iterator = new GlobIterator($this->filesystem, '');

        $this->assertIterator([''], $iterator);
    }

    public function testWildcardMayMatchZeroCharacters()
    {
        $iterator = new GlobIterator($this->filesystem, '/*css');

        $this->assertIterator(
            [
                'base.css',
                'css',
            ],
            $iterator
        );
    }

    public function testDoubleWildcardMayMatchZeroCharacters()
    {
        $iterator = new GlobIterator($this->filesystem, '/**/*css');

        $this->assertIterator(
            [
                'base.css',
                'css', // This one
                'css/old/old_style.css',
                'css/reset.css',
                'css/style.css',
            ],
            $iterator
        );
    }

    public function testWildcardInRoot()
    {
        $iterator = new GlobIterator($this->filesystem, '/*');

        $this->assertIterator(
            [
                'base.css',
                'css',
                'images',
                'js',
            ],
            $iterator
        );
    }

    public function testDoubleWildcardInRoot()
    {
        $iterator = new GlobIterator($this->filesystem, '/**/*');

        $this->assertIterator(
            [
                'base.css',
                'css',
                'css/old',
                'css/old/old_style.css',
                'css/reset.css',
                'css/style.css',
                'images',
                'images/1-top-left.jpg',
                'images/2-top-right.jpg',
                'images/3-bottom-right.jpg',
                'images/4-bottom-left.jpg',
                'images/5-left-top.jpg',
                'images/6-right-top.jpg',
                'images/7-right-bottom.jpg',
                'images/8-left-bottom.jpg',
                'images/nut.svg',
                'js',
                'js/script.js',
            ],
            $iterator
        );
    }

    public function testNoMatches()
    {
        $iterator = new GlobIterator($this->filesystem, '/foo*');

        $this->assertIterator([], $iterator);
    }

    public function testNonExistingBaseDirectory()
    {
        $iterator = new GlobIterator($this->filesystem, '/foo/*');

        $this->assertIterator([], $iterator);
    }
}
