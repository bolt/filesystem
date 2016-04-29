<?php

namespace Bolt\Filesystem\Handler;

/**
 * This represents a filesystem directory.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Directory extends BaseHandler implements DirectoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function isRoot()
    {
        return $this->path === '';
    }

    /**
     * {@inheritdoc}
     */
    public function create($config = [])
    {
        $this->filesystem->createDir($this->path, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->filesystem->deleteDir($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($target, $override = null)
    {
        $this->filesystem->copyDir($this->path, $target, $override);

        return new static($this->filesystem, $target);
    }

    /**
     * {@inheritdoc}
     */
    public function mirror($target, $config = [])
    {
        $this->filesystem->mirror($this->path, $target, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, HandlerInterface $handler = null)
    {
        return $this->filesystem->get($this->path . '/' . $path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($path, FileInterface $handler = null)
    {
        return $this->filesystem->getFile($this->path . '/' . $path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getDir($path)
    {
        return $this->filesystem->getDir($this->path . '/' . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($path)
    {
        return $this->filesystem->getImage($this->path . '/' . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($recursive = false)
    {
        return $this->filesystem->listContents($this->path, $recursive);
    }

    /**
     * {@inheritdoc}
     */
    public function find()
    {
        return $this->filesystem->find()->in($this->path);
    }
}
