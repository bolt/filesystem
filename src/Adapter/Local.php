<?php

namespace Bolt\Filesystem\Adapter;

use Bolt\Common\Thrower;
use Bolt\Filesystem\Capability;
use Bolt\Filesystem\Exception\DirectoryCreationException;
use Bolt\Filesystem\Exception\IncludeFileException;
use Bolt\Filesystem\Handler\Image;
use League\Flysystem\Adapter\Local as LocalBase;
use League\Flysystem\Config;
use League\Flysystem\Util;
use Webmozart\PathUtil\Path;

class Local extends LocalBase implements Capability\ImageInfo, Capability\IncludeFile
{
    /**
     * {@inheritdoc}
     */
    public function __construct($root, $writeFlags = LOCK_EX, $linkHandling = self::DISALLOW_LINKS, array $permissions = [])
    {
        $root = Path::canonicalize($root);
        parent::__construct($root, $writeFlags, $linkHandling, $permissions);
    }

    /**
     * {@inheritdoc}
     */
    protected function ensureDirectory($root)
    {
        try {
            parent::ensureDirectory($root);
        } catch (\Exception $e) {
            throw new DirectoryCreationException($root);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $mimetype = Util::guessMimeType($path, $contents);

        if (!is_writable($location)) {
            return false;
        }

        if (($size = file_put_contents($location, $contents, $this->writeFlags)) === false) {
            return false;
        }

        $type = 'file';

        return compact('type', 'path', 'size', 'contents', 'mimetype');
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $location = $this->applyPathPrefix($path);

        if (!is_writable($location)) {
            return false;
        }

        return unlink($location);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        $location = $this->applyPathPrefix($dirname);
        $umask = umask(0);
        $visibility = $config->get('visibility', 'public');

        if (!is_dir($location) && !@mkdir($location, $this->permissionMap['dir'][$visibility], true)) {
            $return = false;
        } else {
            $return = ['path' => $dirname, 'type' => 'dir'];
        }

        umask($umask);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        $location = $this->applyPathPrefix($dirname);
        if (!is_dir($location) || !is_writable($location)) {
            return false;
        }

        return parent::deleteDir($dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageInfo($path)
    {
        $location = $this->applyPathPrefix($path);

        return Image\Info::createFromFile($location);
    }

    /**
     * {@inheritdoc}
     */
    public function includeFile($path, $once = true)
    {
        $location = $this->applyPathPrefix($path);

        try {
            return Thrower::call(__NAMESPACE__ . '\includeFile' . ($once ? 'Once' : ''), $location);
        } catch (\Exception $e) {
            throw new IncludeFileException($e->getMessage(), $path, 0, $e);
        }
    }
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 *
 * @param string $file
 *
 * @return mixed
 */
function includeFile($file)
{
    /** @noinspection PhpIncludeInspection */
    return include $file;
}

/**
 * Scope isolated include_once.
 *
 * Prevents access to $this/self from included files.
 *
 * @param string $file
 *
 * @return mixed
 */
function includeFileOnce($file)
{
    /** @noinspection PhpIncludeInspection */
    return include_once $file;
}
