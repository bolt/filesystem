<?php

namespace Bolt\Filesystem\Tests\Iterator;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Iterator\ExcludeDirectoryFilterIterator;
use Bolt\Filesystem\Iterator\RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Tests for Bolt\Filesystem\Iterator\ExcludeDirectoryFilterIterator
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ExcludeDirectoryFilterIteratorTest extends IteratorTestCase
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

    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($directories, $expected)
    {
        $it = new RecursiveDirectoryIterator($this->filesystem, 'fixtures');
        $it = new ExcludeDirectoryFilterIterator($it, $directories);
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

        $this->assertIterator($expected, $it);
    }

    public function getAcceptData()
    {
        return [
            'exclude directory name' => [
                ['js'],
                [
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
                ]
            ],
            'partial dir names do not count' => [
                ['j'],
                [
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
                ]
            ],
            'pattern' => [
                ['css/old'],
                [
                    'fixtures/base.css',
                    'fixtures/css',
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
                ]
            ]
        ];
    }
}
