<?php

namespace Bolt\Filesystem\Exception;

/**
 * Exception thrown when a filesystem operation failure happens.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class IOException extends \RuntimeException implements ExceptionInterface
{
    /** @var string|null */
    private $path;

    /**
     * Constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     * @param string|null     $path
     */
    public function __construct($message, $code = 0, \Exception $previous = null, $path = null)
    {
        $this->path = $path;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the associated path for the exception.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }
}
