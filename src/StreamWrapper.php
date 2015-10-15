<?php

namespace Bolt\Filesystem;

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
