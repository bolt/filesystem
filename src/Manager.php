<?php
namespace Bolt\Filesystem;

use InvalidArgumentException;
use League\Flysystem\Handler;
use League\Flysystem\Plugin\PluggableTrait;
use League\Flysystem\PluginInterface;
use LogicException;

class Manager implements AggregateFilesystemInterface, FilesystemInterface
{
    use PluggableTrait;

    /** @var FilesystemInterface[] */
    protected $filesystems = [];

    /**
     * Constructor.
     *
     * @param FilesystemInterface[] $filesystems
     * @param PluginInterface[]     $plugins
     */
    public function __construct(array $filesystems = [], array $plugins = [])
    {
        $this->mountFilesystems($filesystems);
        $this->addPlugins($plugins);
    }

    /**
     * {@inheritdoc}
     */
    public function mountFilesystems(array $filesystems)
    {
        foreach ($filesystems as $prefix => $filesystem) {
            if (!$filesystem instanceof FilesystemInterface) {
                throw new InvalidArgumentException('Filesystem must be instance of Bolt\Filesystem\FilesystemInterface');
            }
            $this->mountFilesystem($prefix, $filesystem);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function mountFilesystem($prefix, FilesystemInterface $filesystem)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects $prefix argument to be a string.');
        }

        $this->filesystems[$prefix] = $filesystem;

        if ($filesystem instanceof MountPointAwareInterface) {
            $filesystem->setMountPoint($prefix);
        }

        // Propagate our plugins to filesystem
        foreach ($this->plugins as $plugin) {
            $filesystem->addPlugin($plugin);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesystem($prefix)
    {
        if (!isset($this->filesystems[$prefix])) {
            throw new LogicException(sprintf('No filesystem mounted with prefix "%s"', $prefix));
        }

        return $this->filesystems[$prefix];
    }

    /**
     * @inheritdoc
     */
    public function hasFilesystem($prefix)
    {
        return isset($this->filesystems[$prefix]);
    }

    /**
     * {@inheritdoc}
     */
    public function addPlugin(PluginInterface $plugin)
    {
        $this->plugins[$plugin->getMethod()] = $plugin;

        // Propagate plugin to all of our filesystems
        foreach ($this->filesystems as $filesystem) {
            $filesystem->addPlugin($plugin);
        }

        return $this;
    }

    /**
     * Register a list of plugins.
     *
     * @param PluginInterface[] $plugins
     */
    public function addPlugins(array $plugins)
    {
        foreach ($plugins as $plugin) {
            if (!$plugin instanceof PluginInterface) {
                throw new InvalidArgumentException('Plugin must be instance of League\Flysystem\PluginInterface');
            }
            $this->addPlugin($plugin);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        list($prefix, $directory) = $this->filterPrefix($directory);
        return $this->getFilesystem($prefix)->listContents($directory, $recursive);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        list($prefixFrom, $pathFrom) = $this->filterPrefix($path);

        $fsFrom = $this->getFilesystem($prefixFrom);
        $buffer = $fsFrom->readStream($pathFrom);

        list($prefixTo, $pathTo) = $this->filterPrefix($newpath);

        $fsTo = $this->getFilesystem($prefixTo);
        $fsTo->writeStream($pathTo, $buffer);

        $buffer->close();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->has($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->read($path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->readStream($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->getSize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->getMimetype($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->getTimestamp($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarbon($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->getCarbon($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->getVisibility($path);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, array $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->write($path, $contents, $config);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, array $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->writeStream($path, $resource, $config);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, array $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->update($path, $contents, $config);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, array $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->updateStream($path, $resource, $config);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->rename($path, $newpath);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->delete($path);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        list($prefix, $path) = $this->filterPrefix($dirname);
        $this->getFilesystem($prefix)->deleteDir($path);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, array $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($dirname);
        $this->getFilesystem($prefix)->createDir($path, $config);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->setVisibility($path, $visibility);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, array $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->put($path, $contents, $config);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($path, $resource, array $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->putStream($path, $resource, $config);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function readAndDelete($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->readAndDelete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, Handler $handler = null)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->get($path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->getImage($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageInfo($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->getImageInfo($path);
    }

    /**
     * {@inheritdoc}
     */
    public function find()
    {
        return new Finder($this);
    }

    /**
     * {@inheritdoc}
     */
    public function includeFile($path, $once = true)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        return $this->getFilesystem($prefix)->includeFile($path, $once);
    }

    /**
     * Separates the filesystem prefix from the path.
     *
     * @param string $path
     *
     * @return array [prefix, path]
     */
    protected function filterPrefix($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('First argument should be a string');
        }

        if (!preg_match('#^.+\:\/\/.*#', $path)) {
            throw new InvalidArgumentException('No prefix detected in path: ' . $path);
        }

        return explode('://', $path, 2);
    }
}
