<?php

namespace Bolt\Filesystem\Tests\Iterator;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Iterator\RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Webmozart\Glob\Glob;
use Webmozart\Glob\Iterator\GlobFilterIterator;

class RecursiveDirectoryIteratorTest extends IteratorTestCase
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

    public function testIteration()
    {
        $it = new RecursiveDirectoryIterator($this->filesystem, 'fixtures');
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

        $expected = [
            'fixtures/base.css',
            'fixtures/css',
            'fixtures/css/old',
            'fixtures/css/old/old_style.css',
            'fixtures/css/reset.css',
            'fixtures/css/style.css',
            'fixtures/images',
            'fixtures/images/1-top-left.jpg',
            'fixtures/images/2-top-right.jpg',
            'fixtures/images/3-bottom-right.jpg',
            'fixtures/images/4-bottom-left.jpg',
            'fixtures/images/5-left-top.jpg',
            'fixtures/images/6-right-top.jpg',
            'fixtures/images/7-right-bottom.jpg',
            'fixtures/images/8-left-bottom.jpg',
            'fixtures/js',
            'fixtures/js/script.js',
        ];
        $this->assertIterator($expected, $it);
        $this->assertIteratorInForeach($expected, $it);
    }

    public function testIterationForGlob()
    {
        $glob = '/fixtures/**/*.css';
        $basePath = Glob::getBasePath($glob);
        $it = new RecursiveDirectoryIterator($this->filesystem, $basePath, RecursiveDirectoryIterator::KEY_FOR_GLOB);
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
        $it = new GlobFilterIterator($glob, $it, GlobFilterIterator::FILTER_KEY | GlobFilterIterator::KEY_AS_KEY);

        $expected = [
            'fixtures/base.css',
            'fixtures/css/old/old_style.css',
            'fixtures/css/reset.css',
            'fixtures/css/style.css',
        ];
        $this->assertIterator($expected, $it);
        $this->assertIteratorInForeach($expected, $it);
    }

    public function testSeek()
    {
        $it = new RecursiveDirectoryIterator($this->filesystem, 'fixtures');

        $it->seek(1);
        $this->assertTrue($it->valid(), 'Current iteration is not valid');
        $this->assertEquals('fixtures/css', $it->current()->getPath());

        $it->seek(0);
        $this->assertTrue($it->valid(), 'Current iteration is not valid');
        $this->assertEquals('fixtures/base.css', $it->current()->getPath());
    }
}
