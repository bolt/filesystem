<?php

namespace Bolt\Filesystem\Cached;

use Bolt\Filesystem\Capability;
use League\Flysystem\Cached\Storage\AbstractCache;
use Psr\SimpleCache\CacheInterface;

class PsrSimpleCache extends AbstractCache implements Capability\ImageInfo
{
    use ImageInfoCacheTrait;

    /** @var CacheInterface */
    private $cache;
    /** @var string */
    private $key;
    /** @var int|null seconds until cache expiration */
    private $expire;

    /**
     * Constructor.
     *
     * @param CacheInterface $cache
     * @param string         $key    storage key
     * @param int|null       $expire seconds until cache expiration
     */
    public function __construct(CacheInterface $cache, $key = 'flysystem', $expire = null)
    {
        $this->cache = $cache;
        $this->key = $key;
        $this->expire = $expire;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->cache->set($this->key, $this->getForStorage(), $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        if ($value = $this->cache->get($this->key)) {
            $this->setFromStorage($value);
        }
    }
}
