<?php

namespace Bolt\Filesystem;

use League\Flysystem;

class Filesystem extends Flysystem\Filesystem implements FilesystemInterface
{
    /**
     * {@inheritdoc}
     */
    public static function cast(Flysystem\FilesystemInterface $filesystem)
    {
        if ($filesystem instanceof Flysystem\Filesystem) {
            return new static($filesystem->getAdapter(), $filesystem->getConfig());
        }

        //TODO create wrapper for interface
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, Flysystem\Handler $handler = null)
    {
        if ($handler === null) {
            $metadata = $this->getMetadata($path);
            $handler = $metadata['type'] === 'file' ? new File($this, $path) : new Directory($this, $path);
        }
        return parent::get($path, $handler);
    }
}
