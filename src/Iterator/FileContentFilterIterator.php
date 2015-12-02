<?php

namespace Bolt\Filesystem\Iterator;

use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Handler\File;
use Symfony\Component\Finder\Iterator\FilecontentFilterIterator as FilecontentFilterIteratorBase;

/**
 * {@inheritdoc}
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class FileContentFilterIterator extends FilecontentFilterIteratorBase
{
    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        if (!$this->matchRegexps && !$this->noMatchRegexps) {
            return true;
        }

        /** @var File $handler */
        $handler = $this->current();

        if (!$handler->isFile()) {
            return false;
        }

        try {
            $content = $handler->read();
        } catch (IOException $e) {
            return false;
        }

        // should at least not matach one rule to exclude
        foreach ($this->noMatchRegexps as $regex) {
            if (preg_match($regex, $content)) {
                return false;
            }
        }

        // should at least match one rule
        $match = true;
        if ($this->matchRegexps) {
            $match = false;
            foreach ($this->matchRegexps as $regex) {
                if (preg_match($regex, $content)) {
                    return true;
                }
            }
        }

        return $match;
    }
}
