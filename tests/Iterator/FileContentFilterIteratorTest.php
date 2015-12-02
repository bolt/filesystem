<?php

namespace Bolt\Filesystem\tests\Iterator;

use Bolt\Filesystem\Adapter\Local;
use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Filesystem;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Handler\Directory;
use Bolt\Filesystem\Handler\File;
use Bolt\Filesystem\Iterator\FileContentFilterIterator;

class FileContentFilterIteratorTest extends IteratorTestCase
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

    public function testAccept()
    {
        $inner = new \ArrayIterator();
        $inner[] = new File($this->filesystem, 'base.css');
        $iterator = new FileContentFilterIterator($inner, [], []);
        $this->assertIterator(['base.css'], $iterator);
    }

    public function testDirectory()
    {
        $inner = new \ArrayIterator();
        $inner[] = new Directory($this->filesystem, 'fixtures');
        $iterator = new FileContentFilterIterator($inner, ['fixtures'], []);
        $this->assertIterator([], $iterator);
    }

    public function testUnreadableFile()
    {
        $inner = new \ArrayIterator();
        $mock = $this->getMock('Bolt\Filesystem\Handler\File', ['read'], [$this->filesystem, 'fixtures/base.css']);
        $mock->expects($this->atLeastOnce())
            ->method('read')
            ->will($this->throwException(new IOException('Fake it, until you make it!')))
        ;
        $inner[] = $mock;
        $iterator = new FileContentFilterIterator($inner, ['fixtures/base.css'], []);
        $this->assertIterator([], $iterator);
    }

    /**
     * @dataProvider getTestFilterData
     */
    public function testFilter(\Iterator $inner, array $matchPatterns, array $noMatchPatterns, array $resultArray)
    {
        $iterator = new FileContentFilterIterator($inner, $matchPatterns, $noMatchPatterns);
        $this->assertIterator($resultArray, $iterator);
    }

    public function getTestFilterData()
    {
        $filesystem = new Filesystem(new Local(__DIR__ . '/../'));
        $inner = new \ArrayIterator();

        $inner[] = new File($filesystem, 'fixtures/base.css');
        $inner[] = new File($filesystem, 'fixtures/css/style.css');
        $inner[] = new File($filesystem, 'fixtures/js/script.js');

        return [
            [$inner, ['.'],              [],                 ['fixtures/base.css', 'fixtures/css/style.css', 'fixtures/js/script.js']],
            [$inner, ['color'],          [],                 ['fixtures/base.css', 'fixtures/css/style.css']],
            [$inner, ['color', 'koala'], ['width', 'shape'], ['fixtures/css/style.css']],
        ];
    }
}
