<?php

namespace Bolt\Filesystem\Tests\Iterator;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Iterator\DateRangeFilterIterator;
use Bolt\Filesystem\Iterator\RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Comparator\DateComparator;

/**
 * Tests for Bolt\Filesystem\Iterator\DateRangeFilterIterator
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DateRangeFilterIteratorTest extends IteratorTestCase
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
    public function testAccept($size, $expected)
    {
        $it = new RecursiveDirectoryIterator($this->filesystem, 'fixtures');
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

        touch(dirname(__DIR__) . '/fixtures/css/old', strtotime('2012-06-14'));
        touch(dirname(__DIR__) . '/fixtures/css/old/old_style.css', strtotime('2012-06-14'));

        $iterator = new DateRangeFilterIterator($it, $size);

        $this->assertIterator($expected, $iterator);
    }

    public function getAcceptData()
    {
        $since20YearsAgo = [
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

        $since2MonthsAgo = [
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
        ];

        $untilLastMonth = [
            'fixtures/css/old',
            'fixtures/css/old/old_style.css',
        ];

        return [
            [[new DateComparator('since 20 years ago')], $since20YearsAgo],
            [[new DateComparator('since 2 months ago')], $since2MonthsAgo],
            [[new DateComparator('until last month')], $untilLastMonth],
        ];
    }
}
