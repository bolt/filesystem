<?php

namespace Bolt\Filesystem\Iterator;

use Bolt\Filesystem\FilesystemInterface;
use RecursiveIteratorIterator;
use Webmozart\Glob\Glob;
use Webmozart\Glob\Iterator\GlobFilterIterator;

/**
 * Returns filesystem handlers matching a glob.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class GlobIterator extends GlobFilterIterator
{
    /**
     * Constructor.
     *
     * @param FilesystemInterface $filesystem The filesystem to search
     * @param string              $glob       The glob pattern
     * @param int                 $flags      A bitwise combination of the flag constants in {@see Glob}
     */
    public function __construct(FilesystemInterface $filesystem, $glob, $flags = 0)
    {
        // Glob code requires absolute paths, so prefix path
        // with leading slash, but not before mount point
        if (strpos($glob, '://') > 0) {
            $glob = str_replace('://', ':///', $glob);
        } else {
            $glob = '/' . ltrim($glob, '/');
        }

        if (!Glob::isDynamic($glob)) {
            // If the glob is a file path, return that path.
            $innerIterator = new \ArrayIterator([$glob => $filesystem->get($glob)]);
        } else {
            $basePath = Glob::getBasePath($glob);

            $innerIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $filesystem,
                    $basePath,
                    RecursiveDirectoryIterator::KEY_FOR_GLOB
                ),
                RecursiveIteratorIterator::SELF_FIRST
            );
        }

        parent::__construct($glob, $innerIterator, static::FILTER_KEY, $flags);
    }
}
