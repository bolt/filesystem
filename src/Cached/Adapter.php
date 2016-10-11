<?php

namespace Bolt\Filesystem\Cached;

use Bolt\Filesystem\Capability;

class Adapter extends \League\Flysystem\Cached\Storage\Adapter implements Capability\ImageInfo
{
    use ImageInfoCacheTrait;
}
