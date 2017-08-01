<?php

namespace Bolt\Filesystem;

use Bolt\Common\Deprecated;
use Bolt\Filesystem\Exception\DumpException;
use Bolt\Filesystem\Exception\ParseException;

/**
 * Json offers methods to parse and dump JSON with error handling.
 *
 * @deprecated since 2.4 and will be removed in 3.0. Use {@see \Bolt\Common\Json} instead.
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
     *
     * @deprecated since 2.4 and will be removed in 3.0. Use {@see \Bolt\Common\Json::parse} instead.
     */
    public static function parse($json, $depth = 512, $options = 0)
    {
        Deprecated::method(2.4, \Bolt\Common\Json::class . '::parse');

        try {
            return \Bolt\Common\Json::parse($json, $options, $depth);
        } catch (\Bolt\Common\Exception\DumpException $e) {
            throw new DumpException($e->getMessage(), $e->getCode(), $e);
        }
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
     *
     * @deprecated since 2.4 and will be removed in 3.0. Use {@see \Bolt\Common\Json::dump} instead.
     */
    public static function dump($data, $options = 448)
    {
        Deprecated::method(2.4, \Bolt\Common\Json::class . '::dump');

        try {
            return \Bolt\Common\Json::dump($data, $options);
        } catch (\Bolt\Common\Exception\ParseException $e) {
            throw new ParseException($e->getRawMessage(), $e->getParsedLine(), $e->getSnippet(), $e);
        }
    }
}
