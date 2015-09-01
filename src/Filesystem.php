<?php

namespace Bolt\Filesystem;

use League\Flysystem;
use LogicException;

class Filesystem extends Flysystem\Filesystem implements FilesystemInterface
{
    /**
     * {@inheritdoc}
     */
    public static function cast(Flysystem\FilesystemInterface $filesystem)
    {
        if (!$filesystem instanceof Flysystem\Filesystem) {
            throw new LogicException('Cannot cast Flysystem\FilesystemInterface, only Flysystem\Filesystem');
        }

        return new static($filesystem->getAdapter(), $filesystem->getConfig());
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

    /**
     * {@inheritdoc}
     */
    public function getImage($path)
    {
        return $this->get($path, new Image());
    }

    /**
     * @inheritDoc
     */
    public function getImageInfo($path)
    {
        return ImageInfo::createFromString($this->read($path));
    }
}
