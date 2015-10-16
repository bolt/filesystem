<?php

namespace Bolt\Filesystem;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use League\Flysystem;

/**
 * Wraps Filesystem into stream protocol.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class StreamWrapper
{
    /** @var resource */
    public $context;

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


    public function url_stat($path, $flags)
    {
    }
}
