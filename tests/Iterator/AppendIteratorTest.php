<?php

namespace Bolt\Filesystem\Tests\Iterator;

use Bolt\Filesystem\Iterator\AppendIterator;

class AppendIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * This asserts that we can identify the bug.
     * The bug being each time append() is called it calls rewind()
     */
    public function testBug()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Bug does not exist on HHVM');
        }

        $this->assertSequence(new \AppendIterator(), '..123.456');
    }

    /**
     * This asserts that our AppendIterator fixes it.
     */
    public function testFix()
    {
        $this->assertSequence(new AppendIterator(), '.123.456');
    }

    private function assertSequence(\AppendIterator $it, $sequence)
    {
        $str = new TestStr();
        $i1 = new TestArrayIterator([1, 2, 3], $str);
        $i2 = new TestArrayIterator([4, 5, 6], $str);

        $it->append($i1);
        $it->append($i2);

        foreach ($it as $item) {
            $str->str .= $item;
        }
        $this->assertSame($sequence, $str->str);
    }
}

class TestArrayIterator extends \ArrayIterator
{
    private $str;

    public function __construct(array $array, TestStr $str)
    {
        parent::__construct($array);
        $this->str = $str;
    }

    public function rewind()
    {
        $this->str->str .= '.';
        parent::rewind();
    }
}

class TestStr
{
    public $str = '';
}
