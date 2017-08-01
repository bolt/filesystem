<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Common\Json;
use Bolt\Filesystem\Exception\DumpException;
use Bolt\Filesystem\Exception\ParseException;

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

        try {
            return Json::parse($contents, $options['flags'], $options['depth']);
        } catch (\Bolt\Common\Exception\ParseException $e) {
            throw new ParseException($e->getRawMessage(), $e->getParsedLine(), $e->getSnippet(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump($contents, $options = [])
    {
        $options += [
            'flags' => 448,
        ];

        try {
            $content = Json::dump($contents, $options['flags']);
        } catch (\Bolt\Common\Exception\DumpException $e) {
            throw new DumpException($e->getMessage(), $e->getCode(), $e);
        }

        $this->put($content);
    }
}
