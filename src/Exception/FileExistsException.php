<?php

namespace Bolt\Filesystem\Exception;

use League\Flysystem;

class FileExistsException extends Flysystem\FileExistsException implements ExceptionInterface
{
}
