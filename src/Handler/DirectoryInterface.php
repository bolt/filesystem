<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Finder;

/**
 * This represents a filesystem directory.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface DirectoryInterface extends HandlerInterface
{
    /**
     * Create the directory.
     *
     * @throws IOException
     */
    public function create();

    /**
     * Get a handler for an entree.
     *
     * @param string           $path    The path to the file.
     * @param HandlerInterface $handler An optional existing handler to populate.
     *
     * @throws IOException
     *
     * @return HandlerInterface
     */
    public function get($path, HandlerInterface $handler = null);

    /**
     * Get a file handler.
     *
     * @param string        $path    The path to the file.
     * @param FileInterface $handler An optional existing file handler to populate.
     *
     * @throws IOException
     *
     * @return FileInterface
     */
    public function getFile($path, FileInterface $handler = null);

    /**
     * Get a directory handler.
     *
     * @param string $path The path to the directory.
     *
     * @throws IOException
     *
     * @return DirectoryInterface
     */
    public function getDir($path);

    /**
     * Get an image handler.
     *
     * @param string $path The path to the image.
     *
     * @throws IOException
     *
     * @return Image
     */
    public function getImage($path);

    /**
     * List the directory contents.
     *
     * @param bool $recursive
     *
     * @return HandlerInterface[]
     */
    public function getContents($recursive = false);

    /**
     * Returns a finder instance set to this directory.
     *
     * @return Finder
     */
    public function find();
}
