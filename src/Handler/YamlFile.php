<?php

namespace Bolt\Filesystem\Handler;

use Bolt\Filesystem\Exception\DumpException;
use Bolt\Filesystem\Exception\ParseException;
use ReflectionMethod;
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
    /** @var bool Whether symfony/yaml is v3.1+ */
    private static $useFlags;

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

        static::checkYamlVersion();

        try {
            if (static::$useFlags) {
                $flags = $this->optionsToFlags($options);

                return Yaml::parse($contents, $flags);
            } else {
                return Yaml::parse(
                    $contents,
                    $options['exceptionsOnInvalidType'],
                    $options['objectSupport'],
                    $options['objectForMap']
                );
            }
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

        static::checkYamlVersion();

        try {
            if (static::$useFlags) {
                $flags = $this->optionsToFlags($options);
                $contents = Yaml::dump($contents, $options['inline'], $options['indent'], $flags);
            } else {
                $contents = Yaml::dump(
                    $contents,
                    $options['inline'],
                    $options['indent'],
                    $options['exceptionsOnInvalidType'],
                    $options['objectSupport']
                );
            }
        } catch (Symfony\DumpException $e) {
            throw new DumpException($e->getMessage(), $e->getCode(), $e);
        }
        $this->put($contents);
    }

    /**
     * @deprecated Remove when symfony/yaml 3.1+ is required
     *
     * @param array $options
     *
     * @return int
     */
    private function optionsToFlags(array $options)
    {
        $flagParams = [
            'exceptionsOnInvalidType' => Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE,
            'objectSupport'           => Yaml::PARSE_OBJECT,
            'objectForMap'            => Yaml::PARSE_OBJECT_FOR_MAP,
        ];

        $flags = 0;
        foreach ($flagParams as $optionName => $bit) {
            if (isset($options[$optionName]) && $options[$optionName]) {
                $flags |= $bit;
            }
        }

        return $flags;
    }

    /**
     * @deprecated Remove when symfony/yaml 3.1+ is required
     */
    private static function checkYamlVersion()
    {
        if (static::$useFlags === null) {
            $ref = new ReflectionMethod(Yaml::class, 'parse');
            static::$useFlags = $ref->getNumberOfParameters() === 2;
        }
    }
}
