<?php

namespace Bolt\Filesystem;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use League\Flysystem;

/**
 * Wraps Filesystem into stream protocol.
 *
 * Based on AWS's S3 implementation.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class StreamWrapper
{
    /**
     * @internal
     * @var resource
     */
    public $context;

    /** @var File Handler of opened stream */
    private $handler;
    /** @var string The opened protocol */
    private $protocol;
    /** @var string The opened path */
    private $path;

    /** @var Cache */
    private $cache;

    /**
     * Registers a stream protocol. If the protocol is already registered, it is replaced.
     *
     * @param FilesystemInterface $filesystem
     * @param string              $protocol
     * @param Cache|null          $cache
     */
    public static function register(FilesystemInterface $filesystem, $protocol = 'flysystem', Cache $cache = null)
    {
        if (in_array($protocol, stream_get_wrappers())) {
            stream_wrapper_unregister($protocol);
        }

        stream_wrapper_register($protocol, get_called_class(), STREAM_IS_URL);

        $default = stream_context_get_options(stream_context_get_default());
        $default[$protocol]['filesystem'] = $filesystem;

        if ($cache) {
            $default[$protocol]['cache'] = $cache;
        } elseif (!isset($default[$protocol]['cache']) || $default[$protocol]['cache'] === null) {
            $default[$protocol]['cache'] = new ArrayCache();
        }

        stream_context_set_default($default);
    }

    /**
     * Removes the stream protocol, if it exists.
     *
     * @param string $protocol
     */
    public static function unregister($protocol)
    {
        if (!in_array($protocol, stream_get_wrappers())) {
            return;
        }
        stream_wrapper_unregister($protocol);

        $default = stream_context_get_options(stream_context_get_default());
        foreach ($default[$protocol] as $key => $value) {
            $default[$protocol][$key] = null;
        }
        stream_context_set_default($default);
    }

    /**
     * Gets a filesystem handler for the given path.
     *
     * @param string $path In the form of protocol://path
     *
     * @return File|Directory
     */
    public static function getHandler($path)
    {
        if (strpos($path, '://') == 0) { // == is intentional to check for false
            throw new \InvalidArgumentException('Path needs to be in the form of protocol://path');
        }
        list($protocol, $path) = explode('://', $path, 2);

        $default = stream_context_get_options(stream_context_get_default());
        if (!isset($default[$protocol]['filesystem']) || $default[$protocol]['filesystem'] === null) {
            throw new \RuntimeException('Filesystem does not exist for that protocol');
        }
        $filesystem = $default[$protocol]['filesystem'];
        if (!$filesystem instanceof FilesystemInterface) {
            throw new \UnexpectedValueException(
                'Filesystem needs to be an instance of Bolt\Filesystem\FilesystemInterface'
            );
        }

        return $filesystem->get($path);
    }

    /**
     * @internal Use {@see StreamWrapper::register} instead.
     */
    public function __construct()
    {
    }

    public function dir_opendir($path, $options)
    {
    }

    public function dir_readdir()
    {
    }

    public function dir_rewinddir()
    {
    }

    public function dir_closedir()
    {
    }


    public function mkdir($path, $mode, $options)
    {
    }

    public function rename($path_from, $path_to)
    {
    }

    public function rmdir($path, $options)
    {
    }

    public function unlink($path)
    {
    }


    public function stream_open($path, $mode, $options, &$opened_path)
    {
    }

    public function stream_close()
    {
    }

    public function stream_flush()
    {
    }

    public function stream_read($count)
    {
    }

    public function stream_write($data)
    {
    }

    public function stream_tell()
    {
    }

    public function stream_eof()
    {
    }

    public function stream_seek($offset, $whence)
    {
    }

    public function stream_stat()
    {
    }

    /**
     * @internal
     *
     * Provides information for is_dir, is_file, filesize, etc.
     *
     * Note: class variables are not populated.
     *
     * @param string $path
     * @param int $flags
     *
     * @return array
     *
     * @link http://php.net/manual/en/streamwrapper.url-stat.php
     */
    public function url_stat($path, $flags)
    {
        $this->init($path);

        // Check if this path is in cache
        if ($value = $this->getCache()->fetch($path)) {
            return $value;
        }

        $handler = $this->getThisHandler($flags);
        if (!$handler || is_array($handler)) {
            return $handler;
        }

        $stat = $this->createStat($handler, $flags);

        if (is_array($stat)) {
            $this->getCache()->save($path, $stat);
        }

        return $stat;
    }

    /**
     * Sets the protocol and path variable for getOptions().
     *
     * @param string $path
     */
    private function init($path)
    {
        list($this->protocol, $this->path) = explode('://', $path, 2);
    }

    /**
     * Creates a url_stat array with the given handler.
     *
     * @param File|Directory $handler
     * @param int            $flags
     *
     * @return array
     */
    private function createStat($handler, $flags = null)
    {
        return $this->boolCall(function () use ($handler) {
            $stat = $this->getStatTemplate();

            if ($handler->isDir()) {
                $stat['mode'] = $stat[2] = 0040777;
            } else {
                $stat['mode'] = $stat[2] = 0100666;
                $stat['size'] = $stat[7] = $handler->getSize();
            }
            $stat['mtime'] = $stat[9] = $stat['ctime'] = $stat[10] = $handler->getTimestamp();

            return $stat;
        }, $flags);
    }

    /**
     * Gets a URL stat template with default values.
     *
     * @return array
     */
    private function getStatTemplate()
    {
        return [
            0  => 0,  'dev'     => 0,
            1  => 0,  'ino'     => 0,
            2  => 0,  'mode'    => 0,
            3  => 0,  'nlink'   => 0,
            4  => 0,  'uid'     => 0,
            5  => 0,  'gid'     => 0,
            6  => -1, 'rdev'    => -1,
            7  => 0,  'size'    => 0,
            8  => 0,  'atime'   => 0,
            9  => 0,  'mtime'   => 0,
            10 => 0,  'ctime'   => 0,
            11 => -1, 'blksize' => -1,
            12 => -1, 'blocks'  => -1,
        ];
    }

    /**
     * @param int $flags
     *
     * @return File|Directory|false|array
     */
    private function getThisHandler($flags = null)
    {
        if (!$this->handler) {
            $this->handler = $this->boolCall(function () {
                return static::getHandler($this->protocol . '://' . $this->path);
            }, $flags);
        }

        return $this->handler;
    }

    private function getCache()
    {
        if (!$this->cache) {
            $this->cache = $this->getOption('cache') ?: new ArrayCache();
        }

        return $this->cache;
    }

    private function getOption($name)
    {
        $options = $this->getOptions();

        return isset($options[$name]) ? $options[$name] : null;
    }

    private function getOptions()
    {
        if ($this->context === null) {
            $options = [];
        } else {
            $options = stream_context_get_options($this->context);
            $options = isset($options[$this->protocol]) ? $options[$this->protocol] : [];
        }

        $default = stream_context_get_options(stream_context_get_default());
        $default = isset($default[$this->protocol]) ? $default[$this->protocol] : [];
        $result = $options + $default;

        return $result;
    }

    private function boolCall(callable $fn, $flags = null)
    {
        try {
            return $fn();
        } catch (\Exception $e) {
            return $this->triggerError($e->getMessage(), $flags);
        }
    }

    /**
     * Triggers one or more errors.
     *
     * @param string|array $errors Errors to trigger
     * @param int $flags If set to STREAM_URL_STAT_QUIET, then no error or exception occurs
     *
     * @return array|bool
     */
    private function triggerError($errors, $flags = null)
    {
        // This is triggered with things like file_exists()
        if ($flags & STREAM_URL_STAT_QUIET) {
            // This is triggered for things like is_link()
            if ($flags & STREAM_URL_STAT_LINK) {
                return $this->getStatTemplate();
            }

            return false;
        }

        // This is triggered when doing things like lstat() or stat()
        trigger_error(implode("\n", (array) $errors), E_USER_WARNING);

        return false;
    }
}
