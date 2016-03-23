<?php

namespace Bolt\Filesystem\Handler;

/**
 * This represents a filesystem file.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class File extends BaseHandler implements FileInterface
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return $this->filesystem->read($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream()
    {
        return $this->filesystem->readStream($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function includeFile($once = true)
    {
        return $this->filesystem->includeFile($this->path, $once);
    }

    /**
     * {@inheritdoc}
     */
    public function write($content)
    {
        $this->filesystem->write($this->path, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($resource)
    {
        $this->filesystem->writeStream($this->path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function update($content)
    {
        $this->filesystem->update($this->path, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($resource)
    {
        $this->filesystem->updateStream($this->path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function put($content)
    {
        $this->filesystem->put($this->path, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($resource)
    {
        $this->filesystem->putStream($this->path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($newPath)
    {
        $this->filesystem->rename($this->path, $newPath);
        $this->path = $newPath;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($target, $override = null)
    {
        $this->filesystem->copy($this->path, $target, $override);

        return new static($this->filesystem, $target);
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->filesystem->delete($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->filesystem->getMimeType($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->filesystem->getSize($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSizeFormatted($si = false)
    {
        $size = $this->getSize();

        if ($si) {
            return $this->getSizeFormattedSi($size);
        } else {
            return $this->getSizeFormattedExact($size);
        }
    }

    /**
     * Format a filesize according to IEC standard. For example: '4734 bytes' -> '4.62 KiB'
     *
     * @param int $size
     *
     * @return string
     */
    private function getSizeFormattedExact($size)
    {
        if ($size > 1024 * 1024) {
            return sprintf('%0.2f MiB', ($size / 1024 / 1024));
        } elseif ($size > 1024) {
            return sprintf('%0.2f KiB', ($size / 1024));
        } else {
            return $size . ' B';
        }
    }

    /**
     * Format a filesize as 'end user friendly', so this should be seen as something that'd
     * be used in a quick glance. For example: '4734 bytes' -> '4.7 kB'
     *
     * @param int $size
     *
     * @return string
     */
    private function getSizeFormattedSi($size)
    {
        if ($size > 1000 * 1000) {
            return sprintf('%0.1f MB', ($size / 1000 / 1000));
        } elseif ($size > 1000) {
            return sprintf('%0.1f KB', ($size / 1000));
        } else {
            return $size . ' B';
        }
    }
}
