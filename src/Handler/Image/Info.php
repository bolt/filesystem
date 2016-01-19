<?php

namespace Bolt\Filesystem\Handler\Image;

use Bolt\Filesystem\Exception\IOException;
use PHPExif\Reader\Reader;
use PHPExif\Reader\ReaderInterface;

/**
 * An object representation of properties returned from getimagesize() and EXIF data
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Info
{
    /** @var Dimensions */
    protected $dimensions;
    /** @var Type */
    protected $type;
    /** @var int */
    protected $bits;
    /** @var int */
    protected $channels;
    /** @var string */
    protected $mime;
    /** @var Exif */
    protected $exif;

    /** @var ReaderInterface */
    protected static $exifReader;

    /**
     * Constructor.
     *
     * @param Dimensions $dimensions
     * @param Type       $type
     * @param int        $bits
     * @param int        $channels
     * @param string     $mime
     * @param Exif       $exif
     */
    public function __construct(Dimensions $dimensions, Type $type, $bits, $channels, $mime, Exif $exif)
    {
        $this->dimensions = $dimensions;
        $this->type = $type;
        $this->bits = (int) $bits;
        $this->channels = (int) $channels;
        $this->mime = $mime;
        $this->exif = $exif;
    }

    /**
     * Creates an empty Info. Useful for when image does not exists to prevent null checks.
     *
     * @return Info
     */
    public static function createEmpty()
    {
        return new static(new Dimensions(0, 0), Type::unknown(), 0, 0, null, new Exif([]));
    }

    /**
     * Creates an Info from a file.
     *
     * @param string $file A filepath
     *
     * @return Info
     */
    public static function createFromFile($file)
    {
        $info = @getimagesize($file);
        if ($info === false) {
            throw new IOException('Failed to get image data from file');
        }

        $exif = static::readExif($file);

        return static::createFromArray($info, $exif);
    }

    /**
     * Creates an Info from a string of image data.
     *
     * @param string $data A string containing the image data
     *
     * @return Info
     */
    public static function createFromString($data)
    {
        $info = @getimagesizefromstring($data);
        if ($info === false) {
            throw new IOException('Failed to get image data from string');
        }

        $file = sprintf('data://%s;base64,%s', $info['mime'], base64_encode($data));
        $exif = static::readExif($file);

        return static::createFromArray($info, $exif);
    }

    /**
     * @param array $info
     * @param Exif  $exif
     *
     * @return Info
     */
    protected static function createFromArray(array $info, Exif $exif)
    {
        // Add defaults to skip isset checks
        $info += [
            0          => 0,
            1          => 0,
            2          => 0,
            'bits'     => 0,
            'channels' => 0,
            'mime'     => '',
        ];

        return new static(
            new Dimensions($info[0], $info[1]),
            Type::getById($info[2]),
            $info['bits'],
            $info['channels'],
            $info['mime'],
            $exif
        );
    }

    /**
     * @param string $file
     *
     * @return Exif
     */
    protected static function readExif($file)
    {
        if (static::$exifReader === null) {
            static::$exifReader = Reader::factory(Reader::TYPE_NATIVE);
        }
        try {
            $exif = static::$exifReader->read($file);

            return Exif::cast($exif);
        } catch (\RuntimeException $e) {
            return new Exif();
        }
    }

    /**
     * Returns the image's dimensions.
     *
     * @return Dimensions
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Returns the image width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->dimensions->getWidth();
    }

    /**
     * Returns the image height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->dimensions->getHeight();
    }

    /**
     * Returns the aspect ratio.
     *
     * @return float
     */
    public function getAspectRatio()
    {
        if ($this->getWidth() === 0 || $this->getHeight() === 0) {
            return 0.0;
        }

        // Account for image rotation
        if (in_array($this->exif->getOrientation(), [5, 6, 7, 8])) {
            return $this->getHeight() / $this->getWidth();
        }

        return $this->getWidth() / $this->getHeight();
    }

    /**
     * Returns whether or not the image is landscape.
     *
     * This is determined by the aspect ratio being
     * greater than 5:4.
     *
     * @return bool
     */
    public function isLandscape()
    {
        return $this->getAspectRatio() >= 1.25;
    }

    /**
     * Returns whether or not the image is portrait.
     *
     * This is determined by the aspect ratio being
     * less than 4:5.
     *
     * @return bool
     */
    public function isPortrait()
    {
        return $this->getAspectRatio() <= 0.8;
    }

    /**
     * Returns whether or not the image is square-ish.
     *
     * The image is considered square if it is not
     * determined to be landscape or portrait.
     *
     * @return bool
     */
    public function isSquare()
    {
        return !$this->isLandscape() && !$this->isPortrait();
    }

    /**
     * Returns the image type.
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the number of bits for each color.
     *
     * @return int
     */
    public function getBits()
    {
        return $this->bits;
    }

    /**
     * Returns the number of channels or colors.
     *
     * 3 for RGB and 4 for CMYK.
     *
     * @return int
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Returns the image's MIME type.
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Returns the image's EXIF data.
     *
     * @return Exif
     */
    public function getExif()
    {
        return $this->exif;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->exif = clone $this->exif;
    }
}
