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

    /** @var array cached metadata */
    protected $metadata;
    /** @var int cached timestamp */
    protected $timestamp;
    /** @var string cached visibility */
    protected $visibility;

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
     * Returns whether the entree exists.
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
        return in_array($this->getType(), ['file', 'image', 'document']);
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
        return $this->getMetadata()['type'];
    }

    /**
     * Get the file's metadata.
     *
     * @param bool $cache Whether to use cached info from previous call
     *
     * @return array
     */
    public function getMetadata($cache = true)
    {
        if (!$cache) {
            $this->metadata = null;
        }
        if (!$this->metadata) {
            $this->metadata = $this->filesystem->getMetadata($this->path);
        }

        return $this->metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($cache = true)
    {
        if (!$cache) {
            $this->timestamp = null;
        }
        if (!$this->timestamp) {
            $this->timestamp = $this->filesystem->getTimestamp($this->path);
        }

        return $this->timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function getCarbon($cache = true)
    {
        return Carbon::createFromTimestamp($this->getTimestamp($cache));
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($cache = true)
    {
        if (!$cache) {
            $this->visibility = null;
        }
        if (!$this->visibility) {
            $this->visibility = $this->filesystem->getVisibility($this->path);
        }

        return $this->visibility;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($visibility)
    {
        $this->filesystem->setVisibility($this->path, $visibility);
        $this->visibility = $visibility;
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
