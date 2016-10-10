<?php

namespace Bolt\Filesystem\Adapter;

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
        if (!is_dir($root)) {
            $umask = umask(0);
            $result = @mkdir($root, $this->permissionMap['dir']['public'], true);
            umask($umask);

            if (!$result) {
                throw new DirectoryCreationException($root);
            }
        }

        return realpath($root);
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

        return compact('path', 'size', 'contents', 'mimetype');
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

        set_error_handler(
            function ($num, $message) use ($path) {
                throw new IncludeFileException($message, $path);
            }
        );

        if ($once) {
            $result = includeFileOnce($location);
        } else {
            $result = includeFile($location);
        }

        restore_error_handler();

        return $result;
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
