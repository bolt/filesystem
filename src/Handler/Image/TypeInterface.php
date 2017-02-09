<?php

namespace Bolt\Filesystem\Handler\Image;

/**
 * An object representation of an image type.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface TypeInterface
{
    /**
     * Returns the ID. (probably IMAGETYPE_* constant).
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the MIME Type associated with this type.
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Returns the file extension for this type.
     *
     * @param bool $includeDot Whether to prepend a dot to the extension or not
     *
     * @return string
     */
    public function getExtension($includeDot = true);

    /**
     * Returns the name of this type.
     *
     * @return string
     */
    public function toString();

    /**
     * Returns the name of this type.
     */
    public function __toString();
}
