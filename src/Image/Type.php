<?php

namespace Bolt\Filesystem\Image;

use InvalidArgumentException;

/**
 * An object representation of an image type.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Type
{
    /** @var int */
    protected $type;

    /**
     * Type constructor.
     *
     * @param int $type An IMAGETYPE_* constant
     */
    public function __construct($type)
    {
        $this->type = (int) $type;

        if (!isset(static::getTypes()[$this->type])) {
            throw new InvalidArgumentException('Given type is not an IMAGETYPE_* constant');
        }
    }

    /**
     * Returns the IMAGETYPE_* constant.
     *
     * @return int
     */
    public function toInt()
    {
        return $this->type;
    }

    /**
     * Returns the Mime-Type associated with this type.
     *
     * @return string
     */
    public function toMimeType()
    {
        return image_type_to_mime_type($this->type);
    }

    /**
     * Returns the file extension for this type.
     *
     * @param bool $includeDot Whether to prepend a dot to the extension or not
     *
     * @return string
     */
    public function toExtension($includeDot = true)
    {
        return image_type_to_extension($this->type, $includeDot);
    }

    /**
     * Returns the name of this type.
     *
     * @return string
     */
    public function toString()
    {
        return static::getTypes()[$this->type];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Returns a list of all the image types.
     *
     * @return array [int, string]
     */
    public static function getTypes()
    {
        // List can't change at runtime, so cache it
        static $types;
        if ($types) {
            return $types;
        }

        // Get list of all standard constants
        $constants = get_defined_constants(true)['standard'];
        // filter down to image type constants
        $types = [];
        foreach ($constants as $name => $value) {
            if ($value !== IMAGETYPE_COUNT && strpos($name, 'IMAGETYPE_') === 0) {
                $types[$name] = $value;
            }
        }
        // flip these and map them to a humanized string
        $types = array_map(
            function ($type) {
                return strtolower(str_replace(['IMAGETYPE_', '_'], ['', ' '], $type));
            },
            array_flip($types)
        );

        return $types;
    }

    /**
     * Returns a list of file extensions for images.
     *
     * Note: Leading periods are excluded.
     *
     * @return string[]
     */
    public static function getTypeExtensions()
    {
        // List can't change at runtime, so cache it
        static $extensions;
        if ($extensions) {
            return $extensions;
        }

        // List of type integers
        $types = array_values(array_flip(static::getTypes()));
        // Remove unknown
        $types = array_filter($types);
        // Map to extension
        $extensions = array_map(
            function ($type) {
                return image_type_to_extension($type, false);
            },
            $types
        );
        // .jpg isn't included :rolleyes:
        $extensions[] = 'jpg';

        return $extensions;
    }
}