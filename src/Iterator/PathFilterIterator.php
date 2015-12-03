<?php

namespace Bolt\Filesystem\Iterator;

use Bolt\Filesystem\Handler\File;
use Symfony\Component\Finder\Iterator\PathFilterIterator as PathFilterIteratorBase;

/**
 * {@inheritdoc}
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class PathFilterIterator extends PathFilterIteratorBase
{
    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        /** @var File $file */
        $file = $this->current();

        $filename = $file->getPath();

        // should at least not match one rule to exclude
        foreach ($this->noMatchRegexps as $regex) {
            if (preg_match($regex, $filename)) {
                return false;
            }
        }

        // should at least match one rule
        $match = true;
        if ($this->matchRegexps) {
            $match = false;
            foreach ($this->matchRegexps as $regex) {
                if (preg_match($regex, $filename)) {
                    return true;
                }
            }
        }

        return $match;
    }
}
