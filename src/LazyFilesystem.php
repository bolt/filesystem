<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\LogicException;

/**
 * A Filesystem which lazily proxies to another filesystem.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class LazyFilesystem implements FilesystemInterface, MountPointAwareInterface
{
    use FilesystemWrapperTrait;

    /** @var callable */
    protected $factory;
    /** @var FilesystemInterface|null */
    protected $filesystem;
    /** @var string|null */
    protected $mountPoint;
    /** @var PluginInterface[] */
    protected $plugins = [];

    /**
     * Constructor.
     *
     * @param callable $factory This callable must return a FilesystemInterface when called.
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    protected function wrapped()
    {
        if (!$this->filesystem) {
            $this->filesystem = call_user_func($this->factory);
            if (!$this->filesystem instanceof FilesystemInterface) {
                throw new LogicException('Factory supplied to LazyFilesystem must return an implementation of FilesystemInterface');
            }

            if ($this->filesystem instanceof MountPointAwareInterface) {
                $this->filesystem->setMountPoint($this->mountPoint);
                $this->mountPoint = null;
            }

            foreach ($this->plugins as $plugin) {
                $this->filesystem->addPlugin($plugin);
            }
            $this->plugins = [];
        }

        return $this->filesystem;
    }

    /**
     * @inheritdoc
     *
     * Plugins are added lazily.
     */
    public function addPlugin(PluginInterface $plugin)
    {
        if ($this->filesystem) {
            $this->filesystem->addPlugin($plugin);
        } else {
            $this->plugins[] = $plugin;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMountPoint()
    {
        $filesystem = $this->wrapped();

        if ($filesystem instanceof MountPointAwareInterface) {
            return $filesystem->getMountPoint();
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * Mount point is set lazily.
     */
    public function setMountPoint($mountPoint)
    {
        if ($this->filesystem) {
            if ($this->filesystem instanceof MountPointAwareInterface) {
                $this->filesystem->setMountPoint($mountPoint);
            }
        } else {
            $this->mountPoint = $mountPoint;
        }
    }
}
