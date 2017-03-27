<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Exception\BadMethodCallException;

/**
 * This represents an image file.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Image extends File implements ImageInterface
{
    /** @var Image\Info */
    protected $info;

    /**
     * {@inheritdoc}
     */
    public function getInfo($cache = true)
    {
        if (!$cache) {
            $this->info = null;
        }
        if (!$this->info) {
            $this->info = $this->filesystem->getImageInfo($this->path);
        }

        return $this->info;
    }

    /**
     * @inheritdoc
     *
     * Use MIME Type from Info as it has handles SVG detection better.
     */
    public function getMimeType()
    {
        return $this->getInfo()->getMime();
    }

    /**
     * Pass-through to plugins, then Image\Info. This is for BC.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        try {
            return parent::__call($method, $arguments);
        } catch (BadMethodCallException $e) {
        }

        $info = $this->getInfo();
        if (method_exists($info, 'get' . $method)) {
            return call_user_func([$info, 'get' . $method]);
        } elseif (method_exists($info, 'is' . $method)) {
            return call_user_func([$info, 'is' . $method]);
        }

        throw $e;
    }
}
