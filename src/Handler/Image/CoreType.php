<?php

namespace Bolt\Filesystem\Handler\Image;

/**
 * A core (built-in) image type.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class CoreType extends Type implements TypeInterface
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;

    /**
     * Returns a list of all the core image types.
     *
     * @return TypeInterface[]
     */
    public static function getTypes()
    {
        $types = [];
        foreach (static::getConstants() as $id => $name) {
            $types[] = new static($id, $name);
        }

        return $types;
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return image_type_to_mime_type($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension($includeDot = true)
    {
        return image_type_to_extension($this->id, $includeDot);
    }

    /**
     * {@inheritdoc}
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
