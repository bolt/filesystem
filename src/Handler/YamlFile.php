<?php

namespace Bolt\Filesystem\Handler;

use Symfony\Component\Yaml\Yaml;

/**
 * File handling for YAML files.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 * @author Carson Full <carsonfull@gmail.com>
 */
class YamlFile extends File implements ParsableInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        return Yaml::parse($this->read());
    }

    /**
     * {@inheritdoc}
     */
    public function dump($content)
    {
        $this->put(Yaml::dump($content));
    }
}
