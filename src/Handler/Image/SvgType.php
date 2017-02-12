<?php

namespace Bolt\Filesystem\Handler\Image;

/**
 * A SVG image type.
 */
final class SvgType extends Type implements TypeInterface
{
    const ID = 101;
    const MIME = 'image/svg+xml';

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return self::MIME;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension($includeDot = true)
    {
        return ($includeDot ? '.' : '') . 'svg';
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'SVG';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->toString();
    }
}
