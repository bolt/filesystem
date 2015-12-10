<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Json;

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
    public function parse($options = [])
    {
        $options += [
            'depth' => 512,
            'flags' => 0,
        ];

        $contents = $this->read();

        return Json::parse($contents, $options['depth'], $options['flags']);
    }

    /**
     * {@inheritdoc}
     */
    public function dump($contents, $options = [])
    {
        $options += [
            'flags' => 448,
        ];

        $content = Json::dump($contents, $options['flags']);
        $this->put($content);
    }
}
