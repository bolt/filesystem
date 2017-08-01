<?php

namespace Bolt\Filesystem\Exception;

use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;

class ParseException extends \Bolt\Common\Exception\ParseException implements ExceptionInterface
{
    /**
     * Constructor.
     *
     * @param string      $message    The error message
     * @param int         $parsedLine The line where the error occurred
     * @param null|string $snippet    The snippet of code near the problem
     * @param \Throwable  $previous   The previous exception
     */
    public function __construct($message, $parsedLine = -1, $snippet = null, $previous = null)
    {
        parent::__construct($message, $parsedLine, $snippet, $previous ? $previous->getCode() : 0, $previous);
    }

    /**
     * Casts Symfony's Yaml ParseException to ours.
     *
     * @param YamlParseException $exception
     *
     * @return ParseException
     */
    public static function castFromYaml(YamlParseException $exception)
    {
        $message = static::parseRawMessage($exception->getMessage());

        return new static($message, $exception->getParsedLine(), $exception->getSnippet(), $exception);
    }

    /**
     * Parse the raw message from Symfony's Yaml ParseException
     *
     * @param string $message
     *
     * @return string
     */
    private static function parseRawMessage($message)
    {
        $dot = false;
        if (substr($message, -1) === '.') {
            $message = substr($message, 0, -1);
            $dot = true;
        }

        if (($pos = strpos($message, ' at line')) > 0) {
            $message = substr($message, 0, $pos);
        } elseif (($pos = strpos($message, ' (near')) > 0) {
            $message = substr($message, 0, $pos);
        }

        if ($dot) {
            $message .= '.';
        }

        return $message;
    }
}
