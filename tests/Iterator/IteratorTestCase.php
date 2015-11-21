<?php

namespace Bolt\Filesystem\Tests\Iterator;

use League\Flysystem;
use Symfony\Component\Filesystem as Symfony;

abstract class IteratorTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var string project root */
    protected $rootDir;
    /** @var string */
    protected $tempDir;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->rootDir = __DIR__ . '/../..';
        $this->tempDir = $this->rootDir . '/tests/temp';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->removeDirectory($this->tempDir);
    }

    protected function removeDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $fs = new Symfony\Filesystem();
        $fs->remove($dir);
    }

    protected function assertIterator($expected, \Traversable $iterator)
    {
        $values = array_map(
            function (Flysystem\Handler $handler) {
                return $handler->getPath();
            },
            iterator_to_array($iterator)
        );

        sort($values);
        sort($expected);

        $this->assertEquals($expected, array_values($values));

        $this->assertIteratorInForeach($expected, $iterator);
    }

    protected function assertOrderedIterator($expected, \Traversable $iterator)
    {
        $values = array_map(
            function (Flysystem\Handler $handler) {
                return $handler->getPath();
            },
            iterator_to_array($iterator)
        );

        $this->assertEquals($expected, array_values($values));
    }

    /**
     * Same as IteratorTestCase::assertIterator with foreach usage.
     *
     * @param array        $expected
     * @param \Traversable $iterator
     */
    protected function assertIteratorInForeach($expected, \Traversable $iterator)
    {
        $values = [];
        foreach ($iterator as $handler) {
            /** @var Flysystem\Handler $handler */
            $this->assertInstanceOf('League\\Flysystem\\Handler', $handler);
            $values[] = $handler->getPath();
        }

        sort($values);
        sort($expected);

        $this->assertEquals($expected, array_values($values));
    }
}
