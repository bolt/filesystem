<?php

namespace Bolt\Filesystem\Handler\Image;

use Bolt\Filesystem\Exception\InvalidArgumentException;

/**
 * An object representation of an image type.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
final class Type
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;

    /** @var Type[] */
    private static $types;

    /**
     * Returns a Type for the ID.
     *
     * @param int $id An IMAGETYPE_* constant
     *
     * @throws InvalidArgumentException If the ID isn't a valid IMAGETYPE_* constant
     *
     * @return Type
     */
    public static function getById($id)
    {
        $id = (int) $id;
        $types = static::getTypes();

        if (!isset($types[$id])) {
            throw new InvalidArgumentException('Given type is not an IMAGETYPE_* constant');
        }

        return $types[$id];
    }

    /**
     * Returns a list of all the image types.
     *
     * @return Type[]
     */
    public static function getTypes()
    {
        if (static::$types === null) {
            foreach (static::getConstants() as $id => $name) {
                static::$types[$id] = new static($id, $name);
            }
        }

        return static::$types;
    }

    /**
     * Returns a list of all the MIME Types for images.
     *
     * @return string[]
     */
    public static function getMimeTypes()
    {
        return array_map(
            function (Type $type) {
                return $type->getMimeType();
            },
            static::getTypes()
        );
    }

    /**
     * Returns a list of all the file extensions for images.
     *
     * @param bool $includeDot Whether to prepend a dot to the extension or not
     *
     * @return string[]
     */
    public static function getExtensions($includeDot = false)
    {
        $extensions = array_filter(
            array_map(
                function (Type $type) use ($includeDot) {
                    return $type->getExtension($includeDot);
                },
                static::getTypes()
            )
        );
        $extensions[] = ($includeDot ? '.' : '') . 'jpg';

        return $extensions;
    }

    /**
     * Shortcut for unknown image type.
     *
     * @return Type
     */
    public static function unknown()
    {
        return static::getById(IMAGETYPE_UNKNOWN);
    }

    /**
     * Returns the IMAGETYPE_* constant.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the MIME Type associated with this type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return image_type_to_mime_type($this->id);
    }

    /**
     * Returns the file extension for this type.
     *
     * @param bool $includeDot Whether to prepend a dot to the extension or not
     *
     * @return string
     */
    public function getExtension($includeDot = true)
    {
        return image_type_to_extension($this->id, $includeDot);
    }

    /**
     * Returns the name of this type.
     *
     * @return string
     */
    public function toString()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Constructor.
     *
     * @param int    $id   An IMAGETYPE_* constant
     * @param string $name String representation based on constant
     */
    private function __construct($id, $name)
    {
        $this->id = (int) $id;
        $this->name = $name;
    }

    /**
     * Returns a list of all the image type constants.
     *
     * @return array [int $id, string $name]
     */
    private static function getConstants()
    {
        // Get list of all standard constants
        $constants = get_defined_constants(true);
        if (defined('HHVM_VERSION')) {
            $constants = $constants['Core'];
        } else {
            $constants = $constants['standard'];
        }

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
                return str_replace(['IMAGETYPE_', '_'], ['', ' '], $type);
            },
            array_flip($types)
        );

        ksort($types);

        return $types;
    }
}
