<?php

namespace Bolt\Filesystem\Exception;

use League\Flysystem;

class NotSupportedException extends Flysystem\NotSupportedException implements ExceptionInterface
{
}
