<?php

namespace Bolt\Filesystem\Handler;

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
     * Pass-through to Image\Info, then plugins. This is for BC.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        $info = $this->getInfo();
        if (method_exists($info, 'get' . $method)) {
            return call_user_func([$info, 'get' . $method]);
        } elseif (method_exists($info, 'is' . $method)) {
            return call_user_func([$info, 'is' . $method]);
        } else {
            return parent::__call($method, $arguments);
        }
    }
}
