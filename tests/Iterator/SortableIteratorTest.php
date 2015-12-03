<?php

namespace Bolt\Filesystem\Tests\Iterator;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Handler\File;
use Bolt\Filesystem\Handler\HandlerInterface;
use Bolt\Filesystem\Iterator\SortableIterator;
use Symfony\Component\Finder\Tests\Iterator\Iterator;

/**
 * Tests for Bolt\Filesystem\Iterator\SortableIterator
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SortableIteratorTest extends IteratorTestCase
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

    public function testConstructor()
    {
        try {
            new SortableIterator(new Iterator([]), 'foobar');
            $this->fail('__construct() throws an InvalidArgumentException exception if the mode is not valid');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Bolt\Filesystem\Exception\InvalidArgumentException', $e, '__construct() throws an InvalidArgumentException exception if the mode is not valid');
        }
    }

    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($mode, array $expected)
    {
        $iterator = new \ArrayIterator([
            File::createFromListingEntry($this->filesystem, ['timestamp' => 1, 'path' => 'fixtures/js/script.js']),
            File::createFromListingEntry($this->filesystem, ['timestamp' => 9, 'path' => 'fixtures/css/reset.css']),
            File::createFromListingEntry($this->filesystem, ['timestamp' => 2, 'path' => 'fixtures']),
            File::createFromListingEntry($this->filesystem, ['timestamp' => 8, 'path' => 'fixtures/css/old/old_style.css']),
            File::createFromListingEntry($this->filesystem, ['timestamp' => 4, 'path' => 'fixtures/base.css']),
            File::createFromListingEntry($this->filesystem, ['timestamp' => 7, 'path' => 'fixtures/css/style.css']),
            File::createFromListingEntry($this->filesystem, ['timestamp' => 5, 'path' => 'fixtures/css/old']),
            File::createFromListingEntry($this->filesystem, ['timestamp' => 6, 'path' => 'fixtures/js']),
            File::createFromListingEntry($this->filesystem, ['timestamp' => 3, 'path' => 'fixtures/css']),
        ]);

        $iterator = new SortableIterator($iterator, $mode);
        $this->assertOrderedIterator($expected, $iterator);
    }

    public function getAcceptData()
    {
        return [
            'sort by name' => [
                SortableIterator::SORT_BY_NAME,
                [
                    'fixtures',
                    'fixtures/base.css',
                    'fixtures/css',
                    'fixtures/css/old',
                    'fixtures/css/old/old_style.css',
                    'fixtures/css/reset.css',
                    'fixtures/css/style.css',
                    'fixtures/js',
                    'fixtures/js/script.js',
                ]
            ],
            'sort by type' => [
                SortableIterator::SORT_BY_TYPE,
                [
                    'fixtures',
                    'fixtures/css',
                    'fixtures/css/old',
                    'fixtures/js',
                    'fixtures/base.css',
                    'fixtures/css/old/old_style.css',
                    'fixtures/css/reset.css',
                    'fixtures/css/style.css',
                    'fixtures/js/script.js',
                ]
            ],
            'sort by time' => [
                SortableIterator::SORT_BY_TIME,
                [
                    'fixtures/js/script.js',
                    'fixtures',
                    'fixtures/css',
                    'fixtures/base.css',
                    'fixtures/css/old',
                    'fixtures/js',
                    'fixtures/css/style.css',
                    'fixtures/css/old/old_style.css',
                    'fixtures/css/reset.css',

                ]
            ],
            'sort by call' => [
                function (HandlerInterface $a, HandlerInterface $b) {
                    return strcmp($a->getPath(), $b->getPath());
                },
                [
                    'fixtures',
                    'fixtures/base.css',
                    'fixtures/css',
                    'fixtures/css/old',
                    'fixtures/css/old/old_style.css',
                    'fixtures/css/reset.css',
                    'fixtures/css/style.css',
                    'fixtures/js',
                    'fixtures/js/script.js',
                ]
            ],
        ];
    }
}
