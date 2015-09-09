<?php

namespace Bolt\Filesystem\Exception;

use League\Flysystem;

class FileNotFoundException extends Flysystem\FileNotFoundException implements ExceptionInterface
{
}
