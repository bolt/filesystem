<?php

namespace Bolt\Filesystem\Cached;

use Bolt\Filesystem\Capability;

class Memory extends \League\Flysystem\Cached\Storage\Memory implements Capability\ImageInfo
{
    use ImageInfoCacheTrait;
}
