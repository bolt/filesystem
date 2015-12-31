<?php

namespace Bolt\Filesystem\Exception;

class FileExistsException extends IOException
{
    /**
     * Constructor.
     *
     * @param string          $path
     * @param \Exception|null $previous
     */
    public function __construct($path, \Exception $previous = null)
    {
        parent::__construct('File already exists at path: ' . $path, $path, 0, $previous);
    }
}
