<?php

namespace Bolt\Filesystem\Iterator;

use Bolt\Filesystem\Handler\Directory;
use Bolt\Filesystem\Handler\File;

class ExcludeDirectoryFilterIterator extends \FilterIterator implements \RecursiveIterator
{
    /** @var \Iterator|\RecursiveIterator */
    private $iterator;
    private $isRecursive;
    private $excludedDirs = [];
    private $excludedPattern;

    /**
     * Constructor.
     *
     * @param \Iterator $iterator    The Iterator to filter
     * @param array     $directories An array of directories to exclude
     */
    public function __construct(\Iterator $iterator, array $directories)
    {
        $this->iterator = $iterator;
        $this->isRecursive = $iterator instanceof \RecursiveIterator;
        $patterns = [];
        foreach ($directories as $directory) {
            if (!$this->isRecursive || false !== strpos($directory, '/')) {
                $patterns[] = preg_quote($directory, '#');
            } else {
                $this->excludedDirs[$directory] = true;
            }
        }
        if ($patterns) {
            $this->excludedPattern = '#(?:^|/)(?:' . implode('|', $patterns) . ')(?:/|$)#';
        }

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return bool true if the value should be kept, false otherwise
     */
    public function accept()
    {
        /** @var Directory|File $handler */
        $handler = $this->iterator->current();
        if ($this->isRecursive && isset($this->excludedDirs[$handler->getFilename()]) && $handler->isDir()) {
            return false;
        }

        if ($this->excludedPattern) {
            $path = $handler->isDir() ? $handler->getPath() : $handler->getDirname();
            $path = str_replace('\\', '/', $path);

            return !preg_match($this->excludedPattern, $path);
        }

        return true;
    }

    public function hasChildren()
    {
        return $this->isRecursive && $this->iterator->hasChildren();
    }

    public function getChildren()
    {
        $children = new self($this->iterator->getChildren(), []);
        $children->excludedDirs = $this->excludedDirs;
        $children->excludedPattern = $this->excludedPattern;

        return $children;
    }
}
