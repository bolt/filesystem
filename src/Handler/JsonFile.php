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
    public function parse()
    {
        $contents = $this->read();
        return Json::parse($contents);
    }

    /**
     * {@inheritdoc}
     */
    public function dump($content)
    {
        $content = Json::dump($content);
        $this->put($content);
    }
}
