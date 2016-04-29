<?php

namespace Bolt\Filesystem\Capability;

use Bolt\Filesystem\Exception\IncludeFileException;

/**
 * Support for loading/including PHP files.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface IncludeFile
{
    /**
     * Load a PHP file.
     *
     * @param string $path The file to include.
     * @param bool   $once Whether to include the file only once.
     *
     * @throws IncludeFileException On failure.
     *
     * @return mixed Returns the return from the file or true if $once is true and this is a subsequent call.
     */
    public function includeFile($path, $once = true);
}
