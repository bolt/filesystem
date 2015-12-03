<?php
namespace Bolt\Filesystem;

use Bolt\Filesystem\Handler\FileInterface;
use Bolt\Filesystem\Handler\HandlerInterface;
use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\Exception\LogicException;

class Manager implements AggregateFilesystemInterface, FilesystemInterface
{
    use Plugin\PluggableTrait;

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
    public function copy($path, $newPath)
    {
        list($prefixFrom, $pathFrom) = $this->filterPrefix($path);

        $fsFrom = $this->getFilesystem($prefixFrom);
        $buffer = $fsFrom->readStream($pathFrom);

        list($prefixTo, $pathTo) = $this->filterPrefix($newPath);

        $fsTo = $this->getFilesystem($prefixTo);
        $fsTo->writeStream($pathTo, $buffer);

        $buffer->close();
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

        return $this->getFilesystem($prefix)->getMimeType($path);
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
    public function write($path, $contents, $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->writeStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->update($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->updateStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->rename($path, $newPath);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        list($prefix, $path) = $this->filterPrefix($dirname);
        $this->getFilesystem($prefix)->deleteDir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($dirname);
        $this->getFilesystem($prefix)->createDir($path, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->setVisibility($path, $visibility);
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->put($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($path, $resource, $config = [])
    {
        list($prefix, $path) = $this->filterPrefix($path);
        $this->getFilesystem($prefix)->putStream($path, $resource, $config);
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
    public function get($path, HandlerInterface $handler = null)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        return $this->getFilesystem($prefix)->get($path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($path, FileInterface $handler = null)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        return $this->getFilesystem($prefix)->get($path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getDir($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        return $this->getFilesystem($prefix)->get($path);
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
