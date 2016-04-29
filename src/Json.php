<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\DumpException;
use Bolt\Filesystem\Exception\ParseException;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * Json offers methods to parse and dump JSON with error handling.
 */
final class Json
{
    /**
     * Parses JSON into a PHP array.
     *
     * @param string $json    The JSON string
     * @param int    $depth   Recursion depth
     * @param int    $options Bitmask of JSON decode options
     *
     * @throws ParseException If the JSON is not valid
     *
     * @return array
     */
    public static function parse($json, $depth = 512, $options = 0)
    {
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true, $depth, $options);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            self::validateSyntax($json);
        }

        return $data;
    }

    /**
     * Dumps a array/object into a JSON string.
     *
     * @param mixed $data    Data to encode into a formatted JSON string
     * @param int   $options json_encode options
     *                       (defaults to JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
     *
     * @throws DumpException If dumping fails
     *
     * @return string
     */
    public static function dump($data, $options = 448)
    {
        $json = json_encode($data, $options);
        if ($json === false) {
            throw new DumpException('JSON dumping failed: ' . static::errorToString(json_last_error()));
        }

        // compact brackets to follow recent php versions
        if (PHP_VERSION_ID < 50428 ||
            (
                PHP_VERSION_ID >= 50500 &&
                PHP_VERSION_ID < 50512
            ) ||
            (
                defined('JSON_C_VERSION') &&
                version_compare(phpversion('json'), '1.3.6', '<')
            )
        ) {
            $json = preg_replace('/\[\s+\]/', '[]', $json);
            $json = preg_replace('/\{\s+\}/', '{}', $json);
        }

        return $json;
    }

    /**
     * Validates the syntax of a JSON string.
     *
     * @param string $json
     *
     * @throws ParseException
     */
    private static function validateSyntax($json)
    {
        $parser = new JsonParser();
        try {
            $parser->parse($json);
        } catch (ParsingException $e) {
            throw ParseException::castFromJson($e);
        }
        if (json_last_error() === JSON_ERROR_UTF8) {
            throw new ParseException('JSON parsing failed: ' . static::errorToString(JSON_ERROR_UTF8));
        }
    }

    /**
     * Converts the error code to a readable message.
     *
     * @param int $code return code of json_last_error function
     *
     * @return string
     */
    private static function errorToString($code)
    {
        switch ($code) {
            case JSON_ERROR_DEPTH:          return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH: return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:      return 'Unexpected control character found';
            case JSON_ERROR_UTF8:           return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:                        return 'Unknown error';
        }
    }
}
