<?php
namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\Exception\LogicException;
use Bolt\Filesystem\Handler\FileInterface;
use Bolt\Filesystem\Handler\HandlerInterface;

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
        foreach ($filesystems as $mountPoint => $filesystem) {
            if (!$filesystem instanceof FilesystemInterface) {
                throw new InvalidArgumentException('Filesystem must be instance of Bolt\Filesystem\FilesystemInterface');
            }
            $this->mountFilesystem($mountPoint, $filesystem);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function mountFilesystem($mountPoint, FilesystemInterface $filesystem)
    {
        if (!is_string($mountPoint)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects $mountPoint argument to be a string.');
        }

        $this->filesystems[$mountPoint] = $filesystem;

        if ($filesystem instanceof MountPointAwareInterface) {
            $filesystem->setMountPoint($mountPoint);
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
    public function getFilesystem($mountPoint)
    {
        if (!isset($this->filesystems[$mountPoint])) {
            throw new LogicException(sprintf('No filesystem with mount point "%s"', $mountPoint));
        }

        return $this->filesystems[$mountPoint];
    }

    /**
     * @inheritdoc
     */
    public function hasFilesystem($mountPoint)
    {
        return isset($this->filesystems[$mountPoint]);
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
        list($mountPoint, $directory) = $this->parsePath($directory);

        return $this->getFilesystem($mountPoint)->listContents($directory, $recursive);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($origin, $target, $override = null)
    {
        list($fsOrigin, $origin) = $this->parsePath($origin);
        list($fsTarget, $target) = $this->parsePath($target);

        $fsOrigin = $this->getFilesystem($fsOrigin);
        $fsTarget = $this->getFilesystem($fsTarget);

        $this->doCopy($fsOrigin, $fsTarget, $origin, $target, $override);
    }

    private function doCopy(FilesystemInterface $fsOrigin, FilesystemInterface $fsTarget, $origin, $target, $override)
    {
        if ($fsTarget->has($target) &&
            (
                $override === false ||
                (
                    $override === null &&
                    $fsOrigin->getTimestamp($origin) <= $fsTarget->getTimestamp($target)
                )
            )
        ) {
            return;
        }

        $buffer = $fsOrigin->readStream($origin);
        $fsTarget->putStream($target, $buffer);

        $buffer->close();
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->has($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->read($path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->readStream($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getType($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getType($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getSize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getMimeType($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getTimestamp($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarbon($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getCarbon($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getVisibility($path);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, $config = [])
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, $config = [])
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->writeStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, $config = [])
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->update($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, $config = [])
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->updateStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath)
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->rename($path, $newPath);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        list($mountPoint, $path) = $this->parsePath($dirname);
        $this->getFilesystem($mountPoint)->deleteDir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, $config = [])
    {
        list($mountPoint, $path) = $this->parsePath($dirname);
        $this->getFilesystem($mountPoint)->createDir($path, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function copyDir($originDir, $targetDir, $override = null)
    {
        $this->mirror($originDir, $targetDir, ['delete' => false, 'override' => $override]);
    }

    /**
     * {@inheritdoc}
     */
    public function mirror($originDir, $targetDir, $config = [])
    {
        $config += [
            'delete'   => true,
            'override' => null,
        ];

        list($fsOrigin, $originDir) = $this->parsePath($originDir);
        list($fsTarget, $targetDir) = $this->parsePath($targetDir);

        $fsOrigin = $this->getFilesystem($fsOrigin);
        $fsTarget = $this->getFilesystem($fsTarget);

        if ($config['delete'] && $fsTarget->has($targetDir)) {
            $it = new Iterator\RecursiveDirectoryIterator($fsTarget, $targetDir);
            $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $handler) {
                /** @var HandlerInterface $handler */
                $origin = str_replace($targetDir, $originDir, $handler->getPath());
                if (!$fsOrigin->has($origin)) {
                    if ($handler->isDir()) {
                        $fsTarget->deleteDir($origin);
                    } else {
                        $fsTarget->delete($origin);
                    }
                }
            }
        }

        if ($fsOrigin->has($originDir)) {
            $fsTarget->createDir($targetDir, $config);
        }

        $it = new Iterator\RecursiveDirectoryIterator($fsOrigin, $originDir);
        $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($it as $handler) {
            $target = str_replace($originDir, $targetDir, $handler->getPath());
            if ($handler->isDir()) {
                $fsTarget->createDir($target, $config);
            } else {
                $this->doCopy($fsOrigin, $fsTarget, $handler->getPath(), $target, $config['override']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->setVisibility($path, $visibility);
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, $config = [])
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->put($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($path, $resource, $config = [])
    {
        list($mountPoint, $path) = $this->parsePath($path);
        $this->getFilesystem($mountPoint)->putStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function readAndDelete($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->readAndDelete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, HandlerInterface $handler = null)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->get($path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($path, FileInterface $handler = null)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getFile($path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getDir($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getDir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getImage($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageInfo($path)
    {
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->getImageInfo($path);
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
        list($mountPoint, $path) = $this->parsePath($path);

        return $this->getFilesystem($mountPoint)->includeFile($path, $once);
    }

    /**
     * Separates the filesystem mount point from the path.
     *
     * @param string $path
     *
     * @return array [mount point, path]
     */
    protected function parsePath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('First argument, $path, should be a string');
        }

        if (!preg_match('#^.+\:\/\/.*#', $path)) {
            throw new InvalidArgumentException('No mount point detected in path: ' . $path);
        }

        return explode('://', $path, 2);
    }
}
