<?php

namespace Bolt\Filesystem\Iterator;

use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\Handler\Directory;
use Bolt\Filesystem\Handler\File;
use Bolt\Filesystem\Handler\HandlerInterface;

/**
 * SortableIterator applies a sort on a given Iterator.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Carson Full <carsonfull@gmail.com>
 */
class SortableIterator implements \IteratorAggregate
{
    const SORT_BY_NAME = 1;
    const SORT_BY_TYPE = 2;
    const SORT_BY_TIME = 3;

    private $iterator;
    private $sort;

    /**
     * Constructor.
     *
     * @param \Traversable $iterator The Iterator to filter
     * @param int|callable $sort     The sort type (SORT_BY_NAME, SORT_BY_TYPE, or a PHP callback)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(\Traversable $iterator, $sort)
    {
        $this->iterator = $iterator;

        if (self::SORT_BY_NAME === $sort) {
            $this->sort = function (HandlerInterface $a, HandlerInterface $b) {
                return strcmp($a->getPath(), $b->getPath());
            };
        } elseif (self::SORT_BY_TYPE === $sort) {
            $this->sort = function (HandlerInterface $a, HandlerInterface $b) {
                if ($a->isDir() && $b->isFile()) {
                    return -1;
                } elseif ($a->isFile() && $b->isDir()) {
                    return 1;
                }

                return strcmp($a->getPath(), $b->getPath());
            };
        } elseif (self::SORT_BY_TIME === $sort) {
            $this->sort = function ($a, $b) {
                /** @var File|Directory $a */
                /** @var File|Directory $b */
                return ($a->getTimestamp() - $b->getTimestamp());
            };
        } elseif (is_callable($sort)) {
            $this->sort = $sort;
        } else {
            throw new InvalidArgumentException('The SortableIterator takes a PHP callable or a valid built-in sort algorithm as an argument.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $array = iterator_to_array($this->iterator, true);
        uasort($array, $this->sort);

        return new \ArrayIterator($array);
    }
}
