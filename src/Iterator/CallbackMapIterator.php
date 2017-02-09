<?php

namespace Bolt\Filesystem\Iterator;

use Traversable;

/**
 * Iterator version of {@see array_map}. This also allows keys to be remapped.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class CallbackMapIterator extends MapIterator
{
    /** @var callable */
    protected $callback;

    /**
     * Constructor.
     *
     * @param array|Traversable $iterable The object to iterate.
     * @param callable          $callback A callback which takes ($value, &$key) and returns the new value.
     */
    public function __construct($iterable, callable $callback)
    {
        parent::__construct($iterable);
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    protected function map($value, &$key)
    {
        $callback = $this->callback;
        // Don't use call_user_func, as the key won't be passed by reference
        return $callback($value, $key);
    }
}
