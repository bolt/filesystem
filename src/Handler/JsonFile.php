<?php

namespace Bolt\Filesystem\Handler;

/**
 * File handling for JSON files.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Carson Full <carsonfull@gmail.com>
 */
class JsonFile extends File implements ParsableInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        return json_decode($this->read(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function dump($content)
    {
        $this->write(json_encode($content));
    }
}
