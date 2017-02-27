<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Handler\FileInterface;
use Bolt\Filesystem\Handler\HandlerInterface;

/**
 * Eases creation of filesystem wrappers.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
trait FilesystemWrapperTrait // implements FilesystemInterface
{
    /**
     * @return FilesystemInterface
     */
    abstract protected function wrapped();

    public function has($path)
    {
        return $this->wrapped()->has($path);
    }

    public function read($path)
    {
        return $this->wrapped()->read($path);
    }

    public function readStream($path)
    {
        return $this->wrapped()->readStream($path);
    }

    public function write($path, $contents, $config = [])
    {
        $this->wrapped()->write($path, $contents, $config);
    }

    public function writeStream($path, $resource, $config = [])
    {
        $this->wrapped()->writeStream($path, $resource, $config);
    }

    public function update($path, $contents, $config = [])
    {
        $this->wrapped()->update($path, $contents, $config);
    }

    public function updateStream($path, $resource, $config = [])
    {
        $this->wrapped()->updateStream($path, $resource, $config);
    }

    public function put($path, $contents, $config = [])
    {
        $this->wrapped()->put($path, $contents, $config);
    }

    public function putStream($path, $resource, $config = [])
    {
        $this->wrapped()->putStream($path, $resource, $config);
    }

    public function readAndDelete($path)
    {
        return $this->wrapped()->readAndDelete($path);
    }

    public function rename($path, $newPath)
    {
        $this->wrapped()->rename($path, $newPath);
    }

    public function copy($origin, $target, $override = null)
    {
        $this->wrapped()->copy($origin, $target, $override);
    }

    public function delete($path)
    {
        $this->wrapped()->delete($path);
    }

    public function deleteDir($dirname)
    {
        $this->wrapped()->deleteDir($dirname);
    }

    public function createDir($dirname, $config = [])
    {
        $this->wrapped()->createDir($dirname, $config);
    }

    public function copyDir($originDir, $targetDir, $override = null)
    {
        $this->wrapped()->copyDir($originDir, $targetDir, $override);
    }

    public function mirror($originDir, $targetDir, $config = [])
    {
        $this->wrapped()->mirror($originDir, $targetDir, $config);
    }

    public function get($path, HandlerInterface $handler = null)
    {
        return $this->wrapped()->get($path, $handler);
    }

    public function getFile($path, FileInterface $handler = null)
    {
        return $this->wrapped()->getFile($path, $handler);
    }

    public function getDir($path)
    {
        return $this->wrapped()->getDir($path);
    }

    public function getImage($path)
    {
        return $this->wrapped()->getImage($path);
    }

    public function getType($path)
    {
        return $this->wrapped()->getType($path);
    }

    public function getSize($path)
    {
        return $this->wrapped()->getSize($path);
    }

    public function getTimestamp($path)
    {
        return $this->wrapped()->getTimestamp($path);
    }

    public function getCarbon($path)
    {
        return $this->wrapped()->getCarbon($path);
    }

    public function getMimeType($path)
    {
        return $this->wrapped()->getMimeType($path);
    }

    public function getVisibility($path)
    {
        return $this->wrapped()->getVisibility($path);
    }

    public function setVisibility($path, $visibility)
    {
        $this->wrapped()->setVisibility($path, $visibility);
    }

    public function listContents($directory = '', $recursive = false)
    {
        return $this->wrapped()->listContents($directory, $recursive);
    }

    public function find()
    {
        return $this->wrapped()->find();
    }

    public function getImageInfo($path)
    {
        return $this->wrapped()->getImageInfo($path);
    }

    public function includeFile($path, $once = true)
    {
        return $this->wrapped()->includeFile($path, $once);
    }

    public function addPlugin(PluginInterface $plugin)
    {
        $this->wrapped()->addPlugin($plugin);
    }
}
