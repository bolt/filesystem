<?php

namespace Bolt\Filesystem\Handler;

/**
 * Interface for files that are parsable.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface ParsableInterface
{
    /**
     * Read and parse the file's contents.
     *
     * @return mixed
     */
    public function parse();

    /**
     * Dump the data to the file.
     *
     * @param mixed $content
     */
    public function dump($content);
}
