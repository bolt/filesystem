<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Finder;

/**
 * This represents a filesystem directory.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Directory extends BaseHandler
{
    /**
     * Create the directory.
     *
     * @throws IOException
     */
    public function create()
    {
        $this->filesystem->createDir($this->path);
    }

    /**
     * Delete the directory.
     *
     * @throws IOException
     */
    public function delete()
    {
        $this->filesystem->deleteDir($this->path);
    }

    /**
     * Get a handler for an entree.
     *
     * @param string           $path    The path to the file.
     * @param HandlerInterface $handler An optional existing handler to populate.
     *
     * @throws IOException
     *
     * @return HandlerInterface
     */
    public function get($path, HandlerInterface $handler = null)
    {
        return $this->filesystem->get($this->path . '/' . $path, $handler);
    }

    /**
     * Get a file handler.
     *
     * @param string        $path    The path to the file.
     * @param FileInterface $handler An optional existing file handler to populate.
     *
     * @throws IOException
     *
     * @return FileInterface
     */
    public function getFile($path, FileInterface $handler = null)
    {
        return $this->filesystem->getFile($this->path . '/' . $path, $handler);
    }

    /**
     * Get a directory handler.
     *
     * @param string $path The path to the directory.
     *
     * @throws IOException
     *
     * @return Directory
     */
    public function getDir($path)
    {
        return $this->filesystem->getDir($this->path . '/' . $path);
    }

    /**
     * List the directory contents.
     *
     * @param bool $recursive
     *
     * @return HandlerInterface[]
     */
    public function getContents($recursive = false)
    {
        return $this->filesystem->listContents($this->path, $recursive);
    }

    /**
     * Returns a finder instance set to this directory.
     *
     * @return Finder
     */
    public function find()
    {
        return $this->filesystem->find()->in($this->path);
    }
}
