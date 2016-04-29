<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Exception\DirectoryCreationException;
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
     * Returns whether this directory is the root directory.
     *
     * @return bool
     */
    public function isRoot();

    /**
     * Create the directory.
     *
     * @param array $config
     *
     * @throws DirectoryCreationException
     * @throws IOException
     */
    public function create($config = []);

    /**
     * Mirrors the directory to another.
     *
     * Note: By default, this will delete files in target if they are not in source.
     *
     * @param string $targetDir The target directory
     * @param array  $config    Valid options are:
     *                          - delete   = Whether to delete files that are not in the source directory (default: true)
     *                          - override = See {@see copyDir}'s $override parameter for details (default: null)
     */
    public function mirror($targetDir, $config = []);

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
     * @return ImageInterface
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
