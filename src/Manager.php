<?php
namespace Bolt\Filesystem;

use InvalidArgumentException;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Handler;
use League\Flysystem\Plugin\PluggableTrait;
use LogicException;

class Manager implements AggregateFilesystemInterface, FilesystemInterface
{
    use PluggableTrait;

    /** @var FilesystemInterface[] */
    protected $filesystems = [];

    /**
     * Constructor.
     *
     * @param array $filesystems
     */
    public function __construct(array $filesystems = [])
    {
        $this->mountFilesystems($filesystems);
    }

    /**
     * {@inheritdoc}
     */
    public function mountFilesystems(array $filesystems)
    {
        foreach ($filesystems as $prefix => $filesystem) {
            if (!$filesystem instanceof FilesystemInterface) {
                $filesystem = $this->createFilesystem($filesystem);
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

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesystem($prefix)
    {
        if (!isset($this->filesystems[$prefix])) {
            throw new LogicException('No filesystem mounted with prefix ' . $prefix);
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
    public function listContents($directory = '', $recursive = false)
    {
        list($prefix, $directory) = $this->filterPrefix($directory);
        $filesystem = $this->getFilesystem($prefix);
        $result = $filesystem->listContents($directory, $recursive);

        foreach ($result as &$file) {
            $file['filesystem'] = $prefix;
        }

        return $result;
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
     * Creates a local filesystem if path exists, else a null filesystem.
     *
     * @param string $path
     *
     * @return Filesystem
     */
    protected function createFilesystem($path)
    {
        return new Filesystem(is_dir($path) ? new Local($path) : new NullAdapter());
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
