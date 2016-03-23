<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Exception\BadMethodCallException;
use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\MountPointAwareTrait;
use Carbon\Carbon;
use League\Flysystem\Util;

/**
 * Base implementation for a filesystem entree.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
abstract class BaseHandler implements HandlerInterface
{
    use MountPointAwareTrait;

    /** @var FilesystemInterface */
    protected $filesystem;
    /** @var string */
    protected $path;

    /**
     * Constructor.
     *
     * @param FilesystemInterface $filesystem
     * @param null                $path
     */
    public function __construct(FilesystemInterface $filesystem = null, $path = null)
    {
        if ($path !== null && !is_string($path)) {
            throw new InvalidArgumentException('Path given must be a string.');
        }

        $this->filesystem = $filesystem;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path given must be a string.');
        }

        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullPath()
    {
        return (!empty($this->mountPoint) ? $this->mountPoint . '://' : '') . $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->filesystem->getDir($this->getDirname());
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    public function getDirname()
    {
        return Util::dirname($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename($suffix = null)
    {
        return basename($this->path, $suffix);
    }

    /**
     * Returns whether the entry exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->filesystem->has($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function isDir()
    {
        return $this->getType() === 'dir';
    }

    /**
     * {@inheritdoc}
     */
    public function isFile()
    {
        return !$this->isDir();
    }

    /**
     * {@inheritdoc}
     */
    public function isImage()
    {
        return $this->getType() === 'image';
    }

    /**
     * {@inheritdoc}
     */
    public function isDocument()
    {
        return $this->getType() === 'document';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->filesystem->getType($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp()
    {
        return $this->filesystem->getTimestamp($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarbon()
    {
        return Carbon::createFromTimestamp($this->getTimestamp());
    }

    /**
     * @inheritDoc
     */
    public function isPublic()
    {
        return $this->getVisibility() === 'public';
    }

    /**
     * @inheritDoc
     */
    public function isPrivate()
    {
        return $this->getVisibility() === 'private';
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility()
    {
        return $this->filesystem->getVisibility($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($visibility)
    {
        $this->filesystem->setVisibility($this->path, $visibility);
    }

    /**
     * Plugins pass-through.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        array_unshift($arguments, $this->path);
        $callback = [$this->filesystem, $method];

        try {
            return call_user_func_array($callback, $arguments);
        } catch (\BadMethodCallException $e) {
            throw new BadMethodCallException(
                'Call to undefined method '
                . get_called_class()
                . '::' . $method
            );
        }
    }
}
