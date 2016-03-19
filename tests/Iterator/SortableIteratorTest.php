<?php

namespace Bolt\Filesystem\Tests\Iterator;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
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
    /** @var Filesystem */
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
        $filesystem = $this->getMock(Filesystem::class, ['getTimestamp'], [$this->filesystem->getAdapter()]);
        $filesystem->method('getTimestamp')
            ->willReturnMap([
                ['fixtures/js/script.js',           1],
                ['fixtures/css/reset.css',          9],
                ['fixtures',                        2],
                ['fixtures/css/old/old_style.css',  8],
                ['fixtures/base.css',               4],
                ['fixtures/css/style.css',          7],
                ['fixtures/css/old',                5],
                ['fixtures/js',                     6],
                ['fixtures/css',                    3],
            ])
        ;

        $iterator = new \ArrayIterator([
            new File($filesystem, 'fixtures/js/script.js'),
            new File($filesystem, 'fixtures/css/reset.css'),
            new File($filesystem, 'fixtures'),
            new File($filesystem, 'fixtures/css/old/old_style.css'),
            new File($filesystem, 'fixtures/base.css'),
            new File($filesystem, 'fixtures/css/style.css'),
            new File($filesystem, 'fixtures/css/old'),
            new File($filesystem, 'fixtures/js'),
            new File($filesystem, 'fixtures/css'),
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
