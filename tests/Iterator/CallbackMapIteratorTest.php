<?php

namespace Bolt\Filesystem\Tests\Iterator;

use ArrayIterator;
use Bolt\Filesystem\Iterator\CallbackMapIterator;

class CallbackMapIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testArray()
    {
        $input = [
            'foo',
            'bar',
        ];
        $it = new CallbackMapIterator($input, function ($value, &$key) {
            // set value to key
            $key = $value;
            // add period to value
            return $value . '.';
        });
        $this->assertInstanceOf(ArrayIterator::class, $it->getInnerIterator());

        $expected = [
            'foo' => 'foo.',
            'bar' => 'bar.',
        ];
        $this->assertEquals($expected, $it->toArray());
        $this->assertEquals($expected, $it->toArray(), 'Should be able to be iterated multiple times');
    }

    public function testIterator()
    {
        $input = new ArrayIterator([
            'foo',
            'bar',
        ]);
        $it = new CallbackMapIterator($input, function ($item) {
            return $item . '.';
        });

        $this->assertEquals(['foo.', 'bar.'], $it->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonIterable()
    {
        new CallbackMapIterator(new \stdClass(), 'var_dump');
    }
}
