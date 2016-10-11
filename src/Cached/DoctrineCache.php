<?php

namespace Bolt\Filesystem\Cached;

use Bolt\Filesystem\Capability;
use Doctrine\Common\Cache\Cache;
use League\Flysystem\Cached\Storage\AbstractCache;

class DoctrineCache extends AbstractCache implements Capability\ImageInfo
{
    use ImageInfoCacheTrait;

    /** @var Cache */
    protected $doctrine;
    /** @var string */
    protected $key;
    /** @var int */
    protected $lifeTime;

    /**
     * Constructor.
     *
     * @param Cache  $cache
     * @param string $key      storage key
     * @param int    $lifeTime seconds until cache expiration
     */
    public function __construct(Cache $cache, $key = 'flysystem', $lifeTime = 0)
    {
        $this->doctrine = $cache;
        $this->key = $key;
        $this->lifeTime = $lifeTime;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $contents = $this->getForStorage();
        $this->doctrine->save($this->key, $contents, $this->lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $contents = $this->doctrine->fetch($this->key);
        if ($contents !== false) {
            $this->setFromStorage($contents);
        }
    }
}
