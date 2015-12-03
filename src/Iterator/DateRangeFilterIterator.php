<?php

namespace Bolt\Filesystem\Iterator;

use Bolt\Filesystem\Handler\Directory;
use Bolt\Filesystem\Handler\File;
use Symfony\Component\Finder\Comparator\DateComparator;

/**
 * DateRangeFilterIterator filters out files that are not in the given date range (last modified dates).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Carson Full <carsonfull@gmail.com>
 */
class DateRangeFilterIterator extends \FilterIterator
{
    private $comparators = [];

    /**
     * Constructor.
     *
     * @param \Iterator        $iterator    The Iterator to filter
     * @param DateComparator[] $comparators An array of DateComparator instances
     */
    public function __construct(\Iterator $iterator, array $comparators)
    {
        $this->comparators = $comparators;

        parent::__construct($iterator);
    }

    public function accept()
    {
        /** @var Directory|File $handler */
        $handler = $this->current();

        if (!$handler->exists()) {
            return false;
        }

        $timestamp = $handler->getTimestamp();
        foreach ($this->comparators as $compare) {
            if (!$compare->test($timestamp)) {
                return false;
            }
        }

        return true;
    }
}
