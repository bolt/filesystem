<?php

namespace Bolt\Filesystem\Adapter;

use Bolt\Filesystem\Capability;
use Bolt\Filesystem\Exception\NotSupportedException;
use League\Flysystem\Cached\CachedAdapter;

/**
 * Cached adapter that supports including files.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Cached extends CachedAdapter implements Capability\IncludeFile
{
    /**
     * {@inheritdoc}
     */
    public function includeFile($path, $once = true)
    {
        $adapter = $this->getAdapter();
        if (!$adapter instanceof Capability\IncludeFile) {
            throw new NotSupportedException('Filesystem does not support including PHP files.', $path);
        }

        return $adapter->includeFile($path, $once);
    }
}
