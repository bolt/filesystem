<?php

namespace Bolt\Filesystem\Handler\Image;

use Bolt\Filesystem\Exception\InvalidArgumentException;

/**
 * A singleton of image types.
 *
 * @final
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Type
{
    /** @var TypeInterface[] */
    private static $types = [];
    /** @var bool */
    private static $initialized = false;

    /**
     * Register an Image Type.
     *
     * @param TypeInterface $type
     */
    public static function register(TypeInterface $type)
    {
        static::$types[$type->getId()] = $type;
    }

    /**
     * Returns a Type for the ID.
     *
     * @param int $id An IMAGETYPE_* constant
     *
     * @throws InvalidArgumentException If the ID isn't a valid IMAGETYPE_* constant
     *
     * @return TypeInterface
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
     * @return TypeInterface[]
     */
    public static function getTypes()
    {
        static::initialize();

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
            function (TypeInterface $type) {
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
                function (TypeInterface $type) use ($includeDot) {
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
     * @return TypeInterface
     */
    public static function unknown()
    {
        return static::getById(IMAGETYPE_UNKNOWN);
    }

    /**
     * Register default types.
     */
    private static function initialize()
    {
        if (static::$initialized) {
            return;
        }
        static::$initialized = true;

        foreach (CoreType::getTypes() as $type) {
            static::register($type);
        }

        static::register(new SvgType());
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
    }
}
