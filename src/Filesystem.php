<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception as Ex;
use Bolt\Filesystem\Handler\FileInterface;
use Bolt\Filesystem\Handler\HandlerInterface;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\StreamWrapper as GuzzleStreamWrapper;
use League\Flysystem;
use Psr\Http\Message\StreamInterface;

/**
 * A filesystem implementation.
 *
 * @author Carson Full <carsonfull@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Filesystem implements FilesystemInterface, MountPointAwareInterface
{
    use MountPointAwareTrait;
    use Flysystem\Plugin\PluggableTrait;
    use Flysystem\ConfigAwareTrait;

    /** @var Flysystem\AdapterInterface */
    protected $adapter;

    /**
     * Constructor.
     *
     * @param Flysystem\AdapterInterface $adapter
     * @param Flysystem\Config|array     $config
     */
    public function __construct(Flysystem\AdapterInterface $adapter, $config = null)
    {
        $this->adapter = $adapter;
        $this->setConfig($config);
    }

    /**
     * Get the Adapter.
     *
     * @return Flysystem\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $path = $this->normalizePath($path);

        try {
            return (bool) $this->getAdapter()->has($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        try {
            $object = $this->getAdapter()->read($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($object === false || !isset($object['contents'])) {
            throw new Ex\IOException('Failed to read file', $path);
        }

        return $object['contents'];
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        try {
            $object = $this->getAdapter()->readStream($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($object === false || !isset($object['stream']) || !is_resource($object['stream'])) {
            throw new Ex\IOException('Failed to open stream', $path);
        }

        /** @var resource $resource */
        $resource = $object['stream'];
        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, $config = [])
    {
        $path = $this->normalizePath($path);
        $this->assertAbsent($path);

        $config = $this->prepareConfig($config);

        try {
            $result = (bool) $this->getAdapter()->write($path, $contents, $config);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to write to file', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, $config = [])
    {
        if ($resource instanceof StreamInterface) {
            $resource = GuzzleStreamWrapper::getResource($resource);
        }
        if (!is_resource($resource)) {
            throw new Ex\InvalidArgumentException(__METHOD__ . ' expects argument #2 to be a valid resource.');
        }

        $path = $this->normalizePath($path);
        $this->assertAbsent($path);

        $config = $this->prepareConfig($config);
        Flysystem\Util::rewindStream($resource);

        try {
            $result = (bool) $this->getAdapter()->writeStream($path, $resource, $config);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to write stream to file', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, $config = [])
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        $config = $this->prepareConfig($config);

        try {
            $result = (bool) $this->getAdapter()->update($path, $contents, $config);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to write to file', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, $config = [])
    {
        if ($resource instanceof StreamInterface) {
            $resource = GuzzleStreamWrapper::getResource($resource);
        }
        if (!is_resource($resource)) {
            throw new Ex\InvalidArgumentException(__METHOD__ . ' expects argument #2 to be a valid resource.');
        }

        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        $config = $this->prepareConfig($config);
        Flysystem\Util::rewindStream($resource);

        try {
            $result = (bool) $this->getAdapter()->updateStream($path, $resource, $config);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to write stream to file', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, $config = [])
    {
        $path = $this->normalizePath($path);
        $config = $this->prepareConfig($config);

        try {
            if ($has = $this->getAdapter()->has($path)) {
                $result = (bool) $this->getAdapter()->update($path, $contents, $config);
            } else {
                $result = (bool) $this->getAdapter()->write($path, $contents, $config);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            $op = $has ? 'update' : 'write';
            throw new Ex\IOException("Failed to $op to file", $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($path, $resource, $config = [])
    {
        if ($resource instanceof StreamInterface) {
            $resource = GuzzleStreamWrapper::getResource($resource);
        }
        if (!is_resource($resource)) {
            throw new Ex\InvalidArgumentException(__METHOD__ . ' expects argument #2 to be a valid resource.');
        }

        $path = $this->normalizePath($path);

        $config = $this->prepareConfig($config);
        Flysystem\Util::rewindStream($resource);

        try {
            if ($has = $this->getAdapter()->has($path)) {
                $result = (bool) $this->getAdapter()->updateStream($path, $resource, $config);
            } else {
                $result = (bool) $this->getAdapter()->writeStream($path, $resource, $config);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            $op = $has ? 'update' : 'write';
            throw new Ex\IOException("Failed to $op stream to file", $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readAndDelete($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        $contents = $this->read($path);

        $this->delete($path);

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath)
    {
        $path = $this->normalizePath($path);
        $newPath = $this->normalizePath($newPath);
        $this->assertPresent($path);
        $this->assertAbsent($newPath);

        try {
            $result = (bool) $this->getAdapter()->rename($path, $newPath);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to rename file', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newPath)
    {
        $path = $this->normalizePath($path);
        $newPath = $this->normalizePath($newPath);
        $this->assertPresent($path);
        $this->assertAbsent($newPath);

        try {
            $result = (bool) $this->getAdapter()->copy($path, $newPath);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to copy file', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        try {
            $result = (bool) $this->getAdapter()->delete($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to delete file', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        $dirname = $this->normalizePath($dirname);
        if ($dirname === '') {
            throw new Ex\RootViolationException('Root directories can not be deleted.');
        }

        try {
            $result = (bool) $this->getAdapter()->deleteDir($dirname);
        } catch (Exception $e) {
            throw $this->handleEx($e, $dirname);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to delete directory', $dirname);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, $config = [])
    {
        $dirname = $this->normalizePath($dirname);
        $config = $this->prepareConfig($config);

        try {
            $result = (bool) $this->getAdapter()->createDir($dirname, $config);
        } catch (Exception $e) {
            throw $this->handleEx($e, $dirname);
        }

        if ($result === false) {
            throw new Ex\DirectoryCreationException($dirname);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, HandlerInterface $handler = null)
    {
        $path = $this->normalizePath($path);

        if ($handler === null) {
            $metadata = $this->getMetadata($path);
            if ($metadata['type'] === 'dir') {
                $handler = new Handler\Directory($this, $path);
            } elseif ($metadata['type'] === 'image') {
                $handler = new Handler\Image($this, $path);
            } else {
                $handler = new Handler\File($this, $path);
            }
        }

        $handler->setPath($path);
        $handler->setFilesystem($this);
        if ($handler instanceof MountPointAwareInterface) {
            $handler->setMountPoint($this->mountPoint);
        }

        return $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($path, FileInterface $handler = null)
    {
        return $this->get($path, $handler ?: new Handler\File());
    }

    /**
     * {@inheritdoc}
     */
    public function getDir($path)
    {
        return $this->get($path, new Handler\Directory());
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($path)
    {
        return $this->get($path, new Handler\Image());
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $path = $this->normalizePath($path);

        try {
            $object = $this->getAdapter()->getSize($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($object === false || !isset($object['size']) || !is_numeric($object['size'])) {
            throw new Ex\IOException("Failed to get file's size", $path);
        }

        return (int) $object['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        try {
            $object = $this->getAdapter()->getTimestamp($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($object === false || !isset($object['timestamp'])) {
            throw new Ex\IOException("Failed to get file's timestamp", $path);
        }

        return $object['timestamp'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCarbon($path)
    {
        return Carbon::createFromTimestamp($this->getTimestamp($path));
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        try {
            $object = $this->getAdapter()->getMimetype($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($object === false || !isset($object['mimetype'])) {
            throw new Ex\IOException("Failed to get file's MIME-type", $path);
        }

        return $object['mimetype'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        try {
            $metadata = $this->getAdapter()->getMetadata($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($metadata === false) {
            throw new Ex\IOException("Failed to get file's metadata", $path);
        }

        $ext = pathinfo($metadata['path'], PATHINFO_EXTENSION);
        if (in_array($ext, Handler\Image\Type::getExtensions())) {
            $metadata['type'] = 'image';
        } elseif (in_array($ext, $this->getDocumentExtensions())) {
            $metadata['type'] = 'document';
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageInfo($path)
    {
        return Handler\Image\Info::createFromString($this->read($path));
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        try {
            $object = $this->getAdapter()->getVisibility($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($object === false || !isset($object['visibility'])) {
            throw new Ex\IOException("Failed to get file's visibility", $path);
        }

        return $object['visibility'];
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        $path = $this->normalizePath($path);

        try {
            $result = (bool) $this->getAdapter()->setVisibility($path, $visibility);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException("Failed to set file's visibility", $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        $directory = $this->normalizePath($directory);

        try {
            $contents = $this->getAdapter()->listContents($directory, $recursive);
        } catch (Exception $e) {
            throw $this->handleEx($e, $directory);
        }

        $formatter = new Flysystem\Util\ContentListingFormatter($directory, $recursive);
        $contents = $formatter->formatListing($contents);

        $contents = array_map(
            function ($entry) {
                if ($entry['type'] === 'dir') {
                    $handler = new Handler\Directory($this, $entry['path']);
                } elseif (isset($entry['extension']) && in_array($entry['extension'], Handler\Image\Type::getExtensions())) {
                    $handler = Handler\Image::createFromListingEntry($this, $entry);
                } else {
                    $handler = Handler\File::createFromListingEntry($this, $entry);
                }
                $handler->setMountPoint($this->mountPoint);

                return $handler;
            },
            $contents
        );

        return $contents;
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
        $adapter = $this->getAdapter();

        if (!$adapter instanceof SupportsIncludeFileInterface) {
            throw new Ex\NotSupportedException('Filesystem does not support including PHP files.', $path);
        }

        return $adapter->includeFile($path, $once);
    }

    /**
     * {@inheritdoc}
     */
    protected function assertPresent($path)
    {
        if (!$this->has($path)) {
            throw new Ex\FileNotFoundException($path);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function assertAbsent($path)
    {
        if ($this->has($path)) {
            throw new Ex\FileExistsException($path);
        }
    }

    /**
     * @param Exception $e
     * @param string    $path
     *
     * @return Exception
     */
    protected function handleEx(Exception $e, $path)
    {
        if ($e instanceof Ex\ExceptionInterface) {
            return $e;
        } elseif ($e instanceof \InvalidArgumentException) {
            return new Ex\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        } elseif ($e instanceof \LogicException) {
            return new Ex\LogicException($e->getMessage(), $e->getCode(), $e);
        } elseif ($e instanceof Flysystem\NotSupportedException) {
            return new Ex\NotSupportedException($e->getMessage(), $e->getCode(), $e);
        } else {
            return new Ex\IOException($e->getMessage(), $path, $e->getCode(), $e);
        }
    }

    protected function getDocumentExtensions()
    {
        return $this->getConfig()->get(
            'doc_extensions',
            ['doc', 'docx', 'txt', 'md', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'csv']
        );
    }

    protected function normalizePath($path)
    {
        try {
            return Flysystem\Util::normalizePath($path);
        } catch (\LogicException $e) {
            throw new Ex\RootViolationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
