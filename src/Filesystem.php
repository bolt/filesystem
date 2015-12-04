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
    use Plugin\PluggableTrait;
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
        return $this->doHas($path);
    }

    private function doHas($path)
    {
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

        return $this->doRead($path);
    }

    private function doRead($path)
    {
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

        return $this->doReadStream($path);
    }

    private function doReadStream($path)
    {
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

        $this->doWrite($path, $contents, $config);
    }

    private function doWrite($path, $contents, Flysystem\Config $config)
    {
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
        $resource = $this->normalizeResource($resource, __METHOD__);
        $path = $this->normalizePath($path);
        $this->assertAbsent($path);

        $config = $this->prepareConfig($config);
        Flysystem\Util::rewindStream($resource);

        $this->doWriteStream($path, $resource, $config);
    }

    private function doWriteStream($path, $resource, Flysystem\Config $config)
    {
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

        $this->doUpdate($path, $contents, $config);
    }

    private function doUpdate($path, $contents, Flysystem\Config $config)
    {
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
        $resource = $this->normalizeResource($resource, __METHOD__);
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        $config = $this->prepareConfig($config);
        Flysystem\Util::rewindStream($resource);

        $this->doUpdateStream($path, $resource, $config);
    }

    private function doUpdateStream($path, $resource, Flysystem\Config $config)
    {
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

        $this->doPut($path, $contents, $config);
    }

    private function doPut($path, $contents, Flysystem\Config $config)
    {
        if ($this->doHas($path)) {
            $this->doUpdate($path, $contents, $config);
        } else {
            $this->doWrite($path, $contents, $config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($path, $resource, $config = [])
    {
        $resource = $this->normalizeResource($resource, __METHOD__);
        $path = $this->normalizePath($path);

        $config = $this->prepareConfig($config);
        Flysystem\Util::rewindStream($resource);

        $this->doPutStream($path, $resource, $config);
    }

    private function doPutStream($path, $resource, Flysystem\Config $config)
    {
        if ($this->doHas($path)) {
            $this->doUpdateStream($path, $resource, $config);
        } else {
            $this->doWriteStream($path, $resource, $config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readAndDelete($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        return $this->doReadAndDelete($path);
    }

    private function doReadAndDelete($path)
    {
        $contents = $this->doRead($path);

        $this->doDelete($path);

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

        $this->doRename($path, $newPath);
    }

    private function doRename($path, $newPath)
    {
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
    public function copy($origin, $target, $override = null)
    {
        $origin = $this->normalizePath($origin);
        $target = $this->normalizePath($target);
        $this->assertPresent($origin);

        $this->doCopy($origin, $target, $override);
    }

    private function doCopy($origin, $target, $override)
    {
        if ($this->doHas($target)) {
            if ($override === false || ($override === null && $this->doGetTimestamp($origin) <= $this->doGetTimestamp($target))) {
                return;
            }
            $this->doDelete($target);
        }

        try {
            $result = (bool) $this->getAdapter()->copy($origin, $target);
        } catch (Exception $e) {
            throw $this->handleEx($e, $origin);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to copy file', $origin);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $path = $this->normalizePath($path);
        $this->assertPresent($path);

        $this->doDelete($path);
    }

    private function doDelete($path)
    {
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

        $this->doDeleteDir($dirname);
    }

    private function doDeleteDir($path)
    {
        try {
            $result = (bool) $this->getAdapter()->deleteDir($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\IOException('Failed to delete directory', $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, $config = [])
    {
        $dirname = $this->normalizePath($dirname);
        $config = $this->prepareConfig($config);

        $this->doCreateDir($dirname, $config);
    }

    private function doCreateDir($path, Flysystem\Config $config)
    {
        try {
            $result = (bool) $this->getAdapter()->createDir($path, $config);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        if ($result === false) {
            throw new Ex\DirectoryCreationException($path);
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

        return $this->doGetSize($path);
    }

    private function doGetSize($path)
    {
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

        return $this->doGetTimestamp($path);
    }

    private function doGetTimestamp($path)
    {
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

        return $this->doGetMimeType($path);
    }

    private function doGetMimeType($path)
    {
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

        return $this->doGetMetadata($path);
    }

    private function doGetMetadata($path)
    {
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

        return $this->doGetVisibility($path);
    }

    private function doGetVisibility($path)
    {
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
        $this->doSetVisibility($path, $visibility);
    }

    private function doSetVisibility($path, $visibility)
    {
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
        if (!$this->doHas($path)) {
            throw new Ex\FileNotFoundException($path);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function assertAbsent($path)
    {
        if ($this->doHas($path)) {
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

    protected function normalizeResource($resource, $method)
    {
        if ($resource instanceof StreamInterface) {
            $resource = GuzzleStreamWrapper::getResource($resource);
        } elseif (!is_resource($resource)) {
            throw new Ex\InvalidArgumentException(
                $method . ' expects $resource to be a resource or instance of Psr\Http\Message\StreamInterface.'
            );
        }

        return $resource;
    }
}
