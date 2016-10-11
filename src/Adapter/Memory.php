<?php

namespace Bolt\Filesystem\Adapter;

use Bolt\Filesystem\Capability;
use League\Flysystem\Memory\MemoryAdapter;

/**
 * Memory adapter that supports including files.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class Memory extends MemoryAdapter implements Capability\IncludeFile
{
    private $includedFiles = [];

    /**
     * {@inheritdoc}
     */
    public function includeFile($path, $once = true)
    {
        if ($once && isset($this->includedFiles[$path])) {
            return true;
        }

        $contents = $this->read($path)['contents'];
        $contents = evalContents($contents);

        $this->includedFiles[$path] = true;

        return $contents;
    }
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 *
 * @param string $__data
 *
 * @return mixed
 */
function evalContents($__data)
{
    return eval('?>' . $__data);
}
