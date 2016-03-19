<?php

namespace Bolt\Filesystem\Iterator;

use Bolt\Filesystem\Exception\FileNotFoundException;
use Bolt\Filesystem\Handler\Directory;
use Bolt\Filesystem\Handler\File;
use Bolt\Filesystem\FilesystemInterface;
use RecursiveIterator;
use SeekableIterator;

/**
 * A RecursiveDirectoryIterator for our Filesystem interface.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class RecursiveDirectoryIterator implements RecursiveIterator, SeekableIterator
{
    /**
     * This mode sets keys to the format expected for globbing.
     *
     * Keys will have a leading slash and directories will have a trailing slash.
     *
     *  Normal key:
     *      foo_dir
     *      foo_file
     *
     *  Glob key:
     *      /foo_dir/
     *      /foo_file
     */
    const KEY_FOR_GLOB = 1;

    /** @var FilesystemInterface */
    protected $filesystem;
    /** @var string */
    protected $path;
    /** @var int */
    protected $mode;

    /** @var array */
    protected $contents = [];
    /** @var bool */
    protected $fetched = false;

    /** @var array contents for children */
    protected $children = [];

    /** @var int current position */
    protected $position = -1;
    /** @var string current path */
    protected $key = null;
    /** @var null|Directory|File */
    protected $current = null;

    /**
     * Constructor.
     *
     * @param FilesystemInterface $filesystem
     * @param string              $path
     * @param int                 $mode
     */
    public function __construct(FilesystemInterface $filesystem, $path = '/', $mode = null)
    {
        $this->filesystem = $filesystem;
        $this->path = $path;
        $this->mode = $mode;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return isset($this->contents[$this->position]);
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
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->position++;
        $this->fetch();
        $this->setCurrent();
    }

    /**
     * {@inheritdoc}
     */
    public function seek($position)
    {
        $this->position = $position;
        $this->fetch();
        $this->setCurrent();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = -1;
        $this->next();
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        try {
            if (!$this->current || !$this->current->isDir()) {
                return false;
            }
        } catch (FileNotFoundException $e) {
            return false;
        }

        $path = $this->current->getFullPath();
        if (!isset($this->children[$path])) {
            $this->children[$path] = $this->doFetch($path);
        }

        return count($this->children[$path]) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        $path = $this->current->getFullPath();
        $it = new static($this->filesystem, $path);

        $it->contents = $this->children[$path];
        $it->fetched = true;
        $it->mode = $this->mode;

        return $it;
    }

    /**
     * Fetch contents once.
     */
    protected function fetch()
    {
        if (!$this->fetched) {
            $this->contents = $this->doFetch($this->path);
            $this->fetched = true;
        }
    }

    /**
     * Actually fetch the listing and return it.
     *
     * @param string $path
     *
     * @return Directory[]|File[]
     */
    protected function doFetch($path)
    {
        return $this->filesystem->listContents($path);
    }

    /**
     * Sets the current handler and path.
     */
    protected function setCurrent()
    {
        if (!isset($this->contents[$this->position])) {
            $this->current = null;
            $this->key = null;

            return;
        }

        $this->current = $this->contents[$this->position];

        $path = $this->current->getFullPath();
        if ($this->mode & static::KEY_FOR_GLOB) {
            // Glob code requires absolute paths, so prefix path
            // with leading slash, but not before mount point
            if (strpos($path, '://') > 0) {
                $path = str_replace('://', ':///', $path);
            } else {
                $path = '/' . ltrim($path, '/');
            }
        }
        $this->key = $path;
    }
}
