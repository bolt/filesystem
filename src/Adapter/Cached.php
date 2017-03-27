<?php

namespace Bolt\Filesystem\Adapter;

use Bolt\Filesystem\Capability;
use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Handler\Image;
use Bolt\Filesystem\Exception\NotSupportedException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\CacheInterface;

/**
 * Cached adapter that supports caching image info and including files.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Cached extends CachedAdapter implements Capability\ImageInfo, Capability\IncludeFile
{
    /** @var CacheInterface */
    protected $cache;

    /**
     * {@inheritdoc}
     */
    public function __construct(AdapterInterface $adapter, CacheInterface $cache)
    {
        parent::__construct($adapter, $cache);
        $this->cache = $cache;
    }

    /**
     * Flush the cache.
     */
    public function flush()
    {
        $this->cache->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $result = $this->cache->read($path);

        if ($result !== false && isset($result['contents']) && $result['contents'] !== false) {
            return $result;
        }

        $result = $this->getAdapter()->read($path);

        if ($result) {
            $object = $result + compact('path');
            $this->cache->updateObject($path, $object, true);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageInfo($path)
    {
        // If cache doesn't support image info, just pass through to adapter.
        if (!$this->cache instanceof Capability\ImageInfo) {
            return $this->doGetImageInfo($path);
        }

        // Get from cache.
        $info = $this->cache->getImageInfo($path);
        if ($info !== false) {
            return is_array($info) ? Image\Info::createFromJson($info) : $info;
        }

        // Else from adapter.
        $info = $this->doGetImageInfo($path);

        // Save info from adapter.
        $object = [
            'path'       => $path,
            'image_info' => $info,
        ];
        $this->cache->updateObject($path, $object, true);

        return $info;
    }

    /**
     * Get image info from adapter.
     *
     * @param string $path
     *
     * @return Image\Info
     */
    private function doGetImageInfo($path)
    {
        // Get info from adapter if it's capable.
        $adapter = $this->getAdapter();
        if ($adapter instanceof Capability\ImageInfo) {
            return $adapter->getImageInfo($path);
        }

        // Else fallback to reading image contents and creating info from string.
        $result = $this->read($path);
        if ($result === false || !isset($result['contents'])) {
            throw new IOException('Failed to read file', $path);
        }

        return Image\Info::createFromString($result['contents'], $path);
    }

    /**
     * {@inheritdoc}
     */
    public function includeFile($path, $once = true)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof Capability\IncludeFile) {
            throw new NotSupportedException('Filesystem does not support including PHP files.');
        }

        return $adapter->includeFile($path, $once);
    }
}
