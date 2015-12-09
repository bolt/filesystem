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
     * @param array $options
     *
     * @return mixed
     */
    public function parse($options = []);

    /**
     * Dump the data to the file.
     *
     * @param mixed $contents
     * @param array $options
     */
    public function dump($contents, $options = []);
}
