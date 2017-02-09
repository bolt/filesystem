<?php

namespace Bolt\Filesystem\Iterator;

use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\Handler\HandlerInterface;
use Traversable;

/**
 * Ensures items from iterable given are either handler objects or strings.
 * If strings, they are converted to handler objects with the filesystem given.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class EnsureHandlerIterator extends MapIterator
{
    /** @var FilesystemInterface */
    private $filesystem;

    /**
     * Constructor.
     *
     * @param FilesystemInterface $filesystem
     * @param array|Traversable   $iterable
     */
    public function __construct(FilesystemInterface $filesystem, $iterable)
    {
        parent::__construct($iterable);
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    protected function map($value, &$key)
    {
        if ($value instanceof HandlerInterface) {
            return $value;
        }
        if (is_string($value)) {
            return $this->filesystem->get($value);
        }

        throw new InvalidArgumentException(sprintf('Iterators or arrays given to Finder::append() must give path strings or %s objects.', HandlerInterface::class));
    }
}
