<?php

namespace Bolt\Filesystem;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Psr7\Stream;
use League\Flysystem;
use Psr\Http\Message\StreamInterface;

/**
 * Wraps Filesystem into stream protocol.
 *
 * Direct use of this class is highly discouraged.
 *
 * It should only be used when needing work with
 * 3rd party code that makes direct filesystem calls
 * and can not be easily extended. The whole point of
 * this library is to have an interface to work with,
 * let's stick with that.
 *
 * This code is based on
 * {@link https://github.com/aws/aws-sdk-php AWS S3's} and
 * {@link https://github.com/guzzle/guzzle Guzzle's} implementations.
 * Licenses are
 * {@link https://github.com/aws/aws-sdk-php/blob/master/LICENSE.md here} and
 * {@link https://github.com/guzzle/guzzle/blob/master/LICENSE here}.
 * Thanks Michael Dowling!
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

    /** @var File|Directory for current path */
    private $handler;
    /** @var string The opened protocol */
    private $protocol;
    /** @var string The opened path */
    private $path;

    /** @var \Iterator Iterator used with directory related calls */
    private $iterator;

    /** @var StreamInterface The opened stream */
    private $stream;
    /** @var string The opened stream mode */
    private $mode;

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
     * If the handler is not given, the path needs to exist
     * to determine if it is a directory or file.
     *
     * @param string            $path    In the form of protocol://path
     * @param Flysystem\Handler $handler An optional handler to populate
     *
     * @return Directory|File
     */
    public static function getHandler($path, $handler = null)
    {
        list($protocol, $path) = self::parsePath($path);

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

        return $filesystem->get($path, $handler);
    }


    /**
     * Support for {@see dir()} and {@see opendir()}
     *
     * @param string $path The path to the directory
     *
     * @return bool
     *
     * @see http://www.php.net/manual/en/function.opendir.php
     */
    public function dir_opendir($path)
    {
        $this->init($path);

        $handler = $this->getThisHandler();
        if ($handler === false) {
            return false;
        }

        $this->iterator = new \ArrayIterator($handler->getContents());

        return true;
    }

    /**
     * Used with {@see readdir()}
     *
     * @return bool|string The next filename or false if there is no next file.
     *
     * @link http://www.php.net/manual/en/function.readdir.php
     */
    public function dir_readdir()
    {
        if (!$this->iterator || !$this->iterator->valid()) {
            return false;
        }

        /** @var File|Directory $handler */
        $handler = $this->iterator->current();

        // Cache the object data for quick url_stat lookups used with RecursiveDirectoryIterator
        $key = $this->getFullPath($handler->getPath());
        $stat = $this->createStat($handler);
        $this->getCache()->save($key, $stat);

        $this->iterator->next();

        // To emulate other stream wrappers we need to strip $this->path
        // (current directory open) from $path (file in directory)
        $path = $handler->getPath();
        if ($this->path) {
            $path = substr($path, strlen($this->path) + 1);
        }

        return $path;
    }

    /**
     * Used with {@see rewinddir()}
     *
     * @return bool
     *
     * @link http://www.php.net/manual/en/function.rewinddir.php
     */
    public function dir_rewinddir()
    {
        $this->iterator->rewind();

        return true;
    }

    /**
     * Used with {@see closedir()}
     *
     * @return bool
     *
     * @link http://www.php.net/manual/en/function.closedir.php
     */
    public function dir_closedir()
    {
        $this->iterator = null;
        gc_collect_cycles();

        return true;
    }


    /**
     * Used with {@see mkdir()}
     *
     * @param string $path
     * @param int    $mode
     * @param int    $options Bitwise mask of values. STREAM_MKDIR_RECURSIVE is implied and the only option I can find.
     *
     * @return bool
     */
    public function mkdir($path, $mode, $options)
    {
        $this->init($path);

        return $this->boolCall(
            function () {
                /** @var Directory $dir */
                $dir = $this->getThisHandler(new Directory());

                $dir->create();

                return true;
            }
        );
    }

    /**
     * Used with {@see rename}
     *
     * @param string $path_from
     * @param string $path_to
     *
     * @return bool
     */
    public function rename($path_from, $path_to)
    {
        $this->init($path_from);

        return $this->boolCall(
            function () use ($path_to) {
                $handler = $this->getThisHandler();
                if ($handler === false) {
                    return false;
                }

                list($protocol_to, $path_to) = self::parsePath($path_to);

                $handler->rename($path_to);

                return true;
            }
        );
    }

    /**
     * Used with {@see rmdir()}
     *
     * @param string $path
     * @param int    $options
     *
     * @return bool
     */
    public function rmdir($path, $options)
    {
        return $this->unlink($path);
    }

    /**
     * Used with {@see unlink()}
     *
     * @param $path
     *
     * @return bool
     */
    public function unlink($path)
    {
        $this->init($path);

        return $this->boolCall(
            function () {
                $handler = $this->getThisHandler();
                if ($handler === false) {
                    return false;
                }

                $handler->delete();

                return true;
            }
        );
    }


    /**
     * Used with {@see fopen()} and {@see file_get_contents()}
     *
     * @param string $path        The path to open
     * @param string $mode        The stream mode
     * @param int    $options     STREAM_USE_PATH - Search include_path for relative paths. We don't support this.
     *                            STREAM_REPORT_ERRORS - Trigger errors or not.
     * @param string $opened_path Path to set with STREAM_USE_PATH, since we don't support that it's not used.
     *
     * @return bool
     */
    public function stream_open($path, $mode, $options, /** @noinspection PhpUnusedParameterInspection */&$opened_path)
    {
        $this->init($path);

        if (strpos($mode, '+') !== false) {
            return $this->triggerError('Simultaneous reading and writing is not supported.');
        }

        // Strip binary, and windose text translation flags
        $this->mode = $mode = rtrim($mode, 'bt');

        if (!in_array($mode, ['r', 'w', 'x'])) {
            return $this->triggerError("Mode not supported: {$mode}. Use r, w, x.");
        }

        $handler = $this->getThisHandler(new File(), $options);

        if ($mode === 'x' && $handler->exists()) {
            return $this->triggerError($handler->getPath() . ' already exists.');
        }

        if ($mode === 'r') {
            $this->stream = $handler->readStream();
        } else {
            $this->stream = new Stream(fopen('php://temp', 'r+'));
        }

        return true;
    }

    /**
     * Used with {@see fclose()}
     */
    public function stream_close()
    {
        $this->stream->close();
        $this->stream = null;
    }

    /**
     * Used with {@see fflush()}
     *
     * @return bool
     */
    public function stream_flush()
    {
        if (!$this->stream->isWritable()) {
            return false;
        }

        return $this->boolCall(
            function () {
                if ($this->stream->isSeekable()) {
                    $this->stream->seek(0);
                }

                $this->handler->putStream($this->stream);

                return true;
            }
        );
    }

    /**
     * Used with {@see fread()} and {@see fgets()}
     *
     * @param int $count
     *
     * @return bool|string
     */
    public function stream_read($count)
    {
        // $this->mode instead $this->stream->isReadable() is used intentionally.
        // The write stream is readable and writable, but we want it to appear only writable.
        if ($this->mode !== 'r') {
            return false;
        }

        return $this->boolCall(
            function () use ($count) {
                return $this->stream->read($count);
            }
        );
    }

    /**
     * Used with {@see fwrite()}
     *
     * @param string $data
     *
     * @return int
     */
    public function stream_write($data)
    {
        return $this->boolCall(
            function () use ($data) {
                return $this->stream->write($data);
            }
        ) ?: 0;
    }

    /**
     * Used with {@see ftell()}
     *
     * @return int
     */
    public function stream_tell()
    {
        return $this->boolCall(
            function () {
                return $this->stream->tell();
            }
        );
    }

    /**
     * Used with {@see feof()}
     *
     * @return bool
     */
    public function stream_eof()
    {
        return $this->stream->eof();
    }

    /**
     * Used with {@see fseek()}
     *
     * @param int $offset
     * @param int $whence
     *
     * @return bool
     */
    public function stream_seek($offset, $whence)
    {
        if (!$this->stream->isSeekable()) {
            return false; //@codeCoverageIgnore
        }

        return $this->boolCall(
            function () use ($offset, $whence) {
                $this->stream->seek($offset, $whence);

                return true;
            }
        );
    }

    /**
     * Used with {@see fstat()}
     *
     * @return array
     */
    public function stream_stat()
    {
        $stats = $this->getStatTemplate();

        // $this->mode instead $this->stream->isReadable() is used intentionally.
        // The write stream is readable and writable, but we want it to appear only writable.
        $stats[2] = $stats['mode'] = $this->mode === 'r' ? 0100444 : 0100644;

        $size = $this->stream->getSize();
        $stats[7] = $stats['size'] = $size !== null ? $size : $this->handler->getSize();

        try {
            $stats[9] = $stats['mtime'] = $this->handler->getTimestamp();
        } catch (\Exception $e) { }

        return $stats;
    }

    /**
     * @internal
     *
     * Provides information for is_dir, is_file, filesize, etc.
     *
     * Note: class variables are not populated.
     *
     * @param string $path
     * @param int    $flags
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

        $handler = $this->getThisHandler(null, $flags);
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
     * Sets the protocol and path variables.
     *
     * @param string $path
     */
    private function init($path)
    {
        list($this->protocol, $this->path) = static::parsePath($path);
    }

    /**
     * @param string $path
     *
     * @return string[] [protocol, path]
     */
    private static function parsePath($path)
    {
        if (strpos($path, '://') == 0) { // == is intentional to check for false
            throw new \InvalidArgumentException('Path needs to be in the form of protocol://path');
        }

        return explode('://', $path, 2);
    }

    /**
     * Returns the path with the protocol.
     *
     * @param string $path Optional path to use instead of current.
     *
     * @return string
     */
    private function getFullPath($path = null)
    {
        return $this->protocol . '://' . ($path ?: $this->path);
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
     * @param Flysystem\Handler $handler Optional handler, to skip file exists check
     * @param int  $flags
     *
     * @return array|Directory|File|false
     */
    private function getThisHandler($handler = null, $flags = null)
    {
        if (!$this->handler) {
            $this->handler = $this->boolCall(function () use ($handler) {
                return static::getHandler($this->getFullPath(), $handler);
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
     * @param int          $flags  If set to STREAM_URL_STAT_QUIET, then no error or exception occurs
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
