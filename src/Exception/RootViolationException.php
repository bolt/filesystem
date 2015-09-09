<?php

namespace Bolt\Filesystem\Exception;

use League\Flysystem;

class RootViolationException extends Flysystem\RootViolationException implements ExceptionInterface
{
}
