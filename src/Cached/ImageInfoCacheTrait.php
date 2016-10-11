<?php

namespace Bolt\Filesystem\Cached;

use Bolt\Filesystem\Handler\Image;

/**
 * Added image info getter and update cleanContents to persist it.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
trait ImageInfoCacheTrait
{
    /**
     * @param string $path
     *
     * @return Image\Info|array|false
     */
    public function getImageInfo($path)
    {
        if (isset($this->cache[$path]['image_info'])) {
            return $this->cache[$path]['image_info'];
        }

        return false;
    }

    public function cleanContents(array $contents)
    {
        $cachedProperties = array_flip($this->getPersistedProperties());

        foreach ($contents as $path => $object) {
            if (is_array($object)) {
                $contents[$path] = array_intersect_key($object, $cachedProperties);
            }
        }

        return $contents;
    }

    protected function getPersistedProperties()
    {
        return [
            'path', 'dirname', 'basename', 'extension', 'filename',
            'size', 'mimetype', 'visibility', 'timestamp', 'type',
            'image_info'
        ];
    }
}
