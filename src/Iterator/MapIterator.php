<?php

namespace Bolt\Filesystem\Iterator;

use ArrayIterator;
use Iterator;
use IteratorIterator;
use OuterIterator;
use Traversable;

/**
 * This iterator maps values and/or keys to different values. This class should be extended to
 * implement custom iterator mappings.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
abstract class MapIterator implements OuterIterator
{
    /** @var Iterator */
    private $inner;

    /** @var mixed */
    private $key = null;
    /** @var mixed */
    private $current = null;

    /**
     * Constructor.
     *
     * @param Traversable|array $iterable
     */
    public function __construct($iterable)
    {
        if ($iterable instanceof Traversable) {
            $this->inner = new IteratorIterator($iterable);
        } elseif (is_array($iterable)) {
            $this->inner = new ArrayIterator($iterable);
        } else {
            throw new \InvalidArgumentException('MapIterator must be given an iterable object.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerIterator()
    {
        return $this->inner;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->inner->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->inner->next();
        $this->applyMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->inner->rewind();
        $this->applyMapping();
    }

    /**
     * Return the iterable as an array (with the mapping applied).
     *
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this);
    }

    /**
     * Map the current key and/or value to something else.
     *
     * @param mixed $value The value.
     * @param mixed $key   Key is passed by reference so it can be changed as well.
     *
     * @return mixed The new value.
     */
    abstract protected function map($value, &$key);

    /**
     * Apply mapping functions to key/value if current entry is valid.
     */
    private function applyMapping()
    {
        if (!$this->valid()) {
            return;
        }

        // Store key as local variable since it is passed by reference.
        $key = $this->inner->key();
        $this->current = $this->map($this->inner->current(), $key);
        $this->key = $key;
    }
}
