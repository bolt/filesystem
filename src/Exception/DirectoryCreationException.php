<?php

namespace Bolt\Filesystem\Exception;

/**
 * An error trying to create a directory.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class DirectoryCreationException extends IOException
{
    /**
     * Constructor.
     *
     * @param string     $path
     * @param \Exception $previous
     */
    public function __construct($path, \Exception $previous = null)
    {
        parent::__construct('Failed to create directory: ' . $path, $path, 0, $previous);
    }
}
