<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Exception\DumpException;
use Bolt\Filesystem\Exception\ParseException;
use Symfony\Component\Yaml\Exception as Symfony;
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
    public function parse($options = [])
    {
        $options += [
            'exceptionsOnInvalidType' => false,
            'objectSupport'           => false,
            'objectForMap'            => false,
        ];

        $contents = $this->read();
        try {
            return Yaml::parse(
                $contents,
                $options['exceptionsOnInvalidType'],
                $options['objectSupport'],
                $options['objectForMap']
            );
        } catch (Symfony\ParseException $e) {
            throw ParseException::castFromYaml($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump($contents, $options = [])
    {
        $options += [
            'inline'                  => 2,
            'indent'                  => 4,
            'exceptionsOnInvalidType' => false,
            'objectSupport'           => false,
        ];

        try {
            $contents = Yaml::dump(
                $contents,
                $options['inline'],
                $options['indent'],
                $options['exceptionsOnInvalidType'],
                $options['objectSupport']
            );
        } catch (Symfony\DumpException $e) {
            throw new DumpException($e->getMessage(), $e->getCode(), $e);
        }
        $this->put($contents);
    }
}
