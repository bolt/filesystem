<?php

namespace Bolt\Filesystem\Cached;

use Bolt\Filesystem\Capability;

class Psr6Cache extends \League\Flysystem\Cached\Storage\Psr6Cache implements Capability\ImageInfo
{
    use ImageInfoCacheTrait;
}
