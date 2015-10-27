<?php

namespace Bolt\Filesystem\Iterator;

use Iterator;

/**
 * This fixes a bug in PHP where rewind() and next() are unnecessarily called when appending.
 *
 * @see https://bugs.php.net/bug.php?id=49104
 */
class AppendIterator extends \AppendIterator
{
    /**
     * @inheritdoc
     *
     * Works around the bug in which PHP calls rewind() and next() when appending
     */
    public function append(Iterator $iterator)
    {
        $this->getArrayIterator()->append($iterator);
    }
}
