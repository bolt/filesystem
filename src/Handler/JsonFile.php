<?php

namespace Bolt\Filesystem\Handler;

use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * File handling for JSON files.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Carson Full <carsonfull@gmail.com>
 */
class JsonFile extends File implements ParsableInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        return static::parseJson($this->read(), $this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function dump($content)
    {
        $this->write(static::encode($content));
    }

    /**
     * Encodes an array into (optionally pretty-printed) JSON
     *
     * @param mixed $data    Data to encode into a formatted JSON string
     * @param int   $options json_encode options
     *                       (defaults to JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
     *
     * @return string
     */
    public static function encode($data, $options = 448)
    {
        $json = json_encode($data, $options);
        if ($json === false) {
            self::throwEncodeError(json_last_error());
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
     * Throws an exception according to a given code with a customized message
     *
     * @param int $code return code of json_last_error function
     *
     * @throws \RuntimeException
     */
    private static function throwEncodeError($code)
    {
        switch ($code) {
            case JSON_ERROR_DEPTH:
                $msg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $msg = 'Unexpected control character found';
                break;
            case JSON_ERROR_UTF8:
                $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $msg = 'Unknown error';
        }

        throw new \RuntimeException('JSON encoding failed: ' . $msg);
    }

    /**
     * Parses json string and returns hash.
     *
     * @param string $json json string
     * @param string $file the json file
     *
     * @return mixed
     */
    public static function parseJson($json, $file = null)
    {
        if (null === $json) {
            return null;
        }
        $data = json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            self::validateSyntax($json, $file);
        }

        return $data;
    }

    /**
     * Validates the syntax of a JSON string
     *
     * @param string $json
     * @param string $file
     *
     * @throws \UnexpectedValueException
     * @throws ParsingException
     *
     * @return bool
     */
    protected static function validateSyntax($json, $file = null)
    {
        $parser = new JsonParser();
        $result = $parser->lint($json);
        if ($result === null) {
            if (json_last_error() === JSON_ERROR_UTF8) {
                throw new \UnexpectedValueException(sprintf('"%s" is not UTF-8, could not parse as JSON', $file));
            }

            return true;
        }

        throw new ParsingException(
            sprintf("\"%s\" does not contain valid JSON \n%s", $file, $result->getMessage()),
            $result->getDetails()
        );
    }
}
