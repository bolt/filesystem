<?php

namespace Bolt\Filesystem;

use Bolt\Filesystem\Exception as Ex;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Stream\GuzzleStreamWrapper;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\StreamInterface;
use InvalidArgumentException;
use League\Flysystem;
use LogicException;

class Filesystem extends Flysystem\Filesystem implements FilesystemInterface
{
    private static $DOCUMENT_EXTENSIONS = ['doc', 'docx', 'txt', 'md', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'];

    /**
     * {@inheritdoc}
     */
    public static function cast(Flysystem\FilesystemInterface $filesystem)
    {
        if (!$filesystem instanceof Flysystem\Filesystem) {
            throw new LogicException('Cannot cast Flysystem\FilesystemInterface, only Flysystem\Filesystem');
        }

        return new static($filesystem->getAdapter(), $filesystem->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        try {
            return parent::has($path);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, array $config = [])
    {
        try {
            if (!parent::write($path, $contents, $config)) {
                throw new Ex\IOException('Failed to write to file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, array $config = [])
    {
        if ($resource instanceof StreamInterface) {
            $resource = GuzzleStreamWrapper::getResource($resource);
        }
        try {
            if (!parent::writeStream($path, $resource, $config)) {
                throw new Ex\IOException('Failed to write to file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, array $config = [])
    {
        try {
            if (!parent::put($path, $contents, $config)) {
                throw new Ex\IOException('Failed to write to file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($path, $resource, array $config = [])
    {
        if ($resource instanceof StreamInterface) {
            $resource = GuzzleStreamWrapper::getResource($resource);
        }
        try {
            if (!parent::putStream($path, $resource, $config)) {
                throw new Ex\IOException('Failed to write to file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function readAndDelete($path)
    {
        try {
            $contents = parent::readAndDelete($path);
            if ($contents === false) {
                throw new Ex\IOException('Failed to read file', $path);
            }
            return $contents;
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, array $config = [])
    {
        try {
            if (!parent::update($path, $contents, $config)) {
                throw new Ex\IOException('Failed to write to file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, array $config = [])
    {
        if ($resource instanceof StreamInterface) {
            $resource = GuzzleStreamWrapper::getResource($resource);
        }
        try {
            if (!parent::updateStream($path, $resource, $config)) {
                throw new Ex\IOException('Failed to write to file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        try {
            $contents = parent::read($path);
            if ($contents === false) {
                throw new Ex\IOException('Failed to read file', $path);
            }
            return $contents;
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        try {
            $resource = parent::readStream($path);
            if ($resource === false) {
                throw new Ex\IOException('Failed to open stream', $path);
            }
            return Stream::factory($resource);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        try {
            if (!parent::rename($path, $newpath)) {
                throw new Ex\IOException('Failed to rename file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        try {
            if (!parent::copy($path, $newpath)) {
                throw new Ex\IOException('Failed to copy file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        try {
            if (!parent::delete($path)) {
                throw new Ex\IOException('Failed to delete file', $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        try {
            if (!parent::deleteDir($dirname)) {
                throw new Ex\IOException('Failed to delete directory', $dirname);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $dirname);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, array $config = [])
    {
        try {
            if (!parent::createDir($dirname, $config)) {
                throw new Ex\IOException('Failed to delete file', $dirname);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $dirname);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        try {
            $contents = parent::listContents($directory, $recursive);
        } catch (Exception $e) {
            throw $this->handleEx($e, $directory);
        }

        $contents = array_map(
            function ($entry) {
                if ($entry['type'] === 'dir') {
                    return new Directory($this, $entry['path']);
                } elseif (isset($entry['extension']) && in_array($entry['extension'], Image\Type::getTypeExtensions())) {
                    return Image::createFromListingEntry($this, $entry);
                } else {
                    return File::createFromListingEntry($this, $entry);
                }
            },
            $contents
        );

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        try {
            $mimeType = parent::getMimetype($path);
            if ($mimeType === false) {
                throw new Ex\IOException("Failed to get file's MIME-type", $path);
            }
            return $mimeType;
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        try {
            $ts = parent::getTimestamp($path);
            if ($ts === false) {
                throw new Ex\IOException("Failed to get file's timestamp", $path);
            }
            return $ts;
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        try {
            $visibility = parent::getVisibility($path);
            if ($visibility === false) {
                throw new Ex\IOException("Failed to get file's visibility", $path);
            }
            return $visibility;
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        try {
            $size = parent::getSize($path);
            if ($size === false) {
                throw new Ex\IOException("Failed to get file's size", $path);
            }
            return $size;
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        try {
            if (!parent::setVisibility($path, $visibility)) {
                throw new Ex\IOException("Failed to set file's visibility", $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        try {
            $metadata = parent::getMetadata($path);
            if ($metadata === false) {
                throw new Ex\IOException("Failed to get file's metadata", $path);
            }
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }

        $ext = pathinfo($metadata['path'], PATHINFO_EXTENSION);
        if (in_array($ext, Image\Type::getTypeExtensions())) {
            $metadata['type'] = 'image';
        } elseif (in_array($ext, static::$DOCUMENT_EXTENSIONS)) {
            $metadata['type'] = 'document';
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, Flysystem\Handler $handler = null)
    {
        if ($handler === null) {
            $metadata = $this->getMetadata($path);
            if ($metadata['type'] === 'dir') {
                $handler = new Directory($this, $path);
            } elseif ($metadata['type'] === 'image') {
                $handler = new Image($this, $path);
            } else {
                $handler = new File($this, $path);
            }
        }
        try {
            return parent::get($path, $handler);
        } catch (Exception $e) {
            throw $this->handleEx($e, $path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($path)
    {
        return $this->get($path, new Image());
    }

    /**
     * {@inheritdoc}
     */
    public function getImageInfo($path)
    {
        return Image\Info::createFromString($this->read($path));
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
    public function assertPresent($path)
    {
        if (!$this->has($path)) {
            throw new Ex\FileNotFoundException($path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assertAbsent($path)
    {
        if ($this->has($path)) {
            throw new Ex\FileExistsException($path);
        }
    }

    /**
     * @param Exception $e
     * @param string     $path
     *
     * @return Exception
     */
    protected function handleEx(Exception $e, $path)
    {
        if ($e instanceof InvalidArgumentException) {
            return $e;
        } elseif ($e instanceof Flysystem\RootViolationException) {
            return new Ex\RootViolationException($e->getMessage(), $e->getCode(), $e);
        } elseif ($e instanceof Flysystem\NotSupportedException) {
            return new Ex\NotSupportedException($e->getMessage(), $e->getCode(), $e);
        } elseif ($e instanceof LogicException) {
            if (strpos($e->getMessage(), 'Path is outside of the defined root') === 0) {
                return new Ex\RootViolationException($e->getMessage(), $e->getCode(), $e);
            }
            return new Ex\IOException($e->getMessage(), $e->getCode(), $e, $path);
        } else {
            return new Ex\IOException($e->getMessage(), $e->getCode(), $e, $path);
        }
    }
}
