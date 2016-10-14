<?php

namespace Bolt\Filesystem\Adapter;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Bolt\Filesystem\Exception\IOException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;
use League\Flysystem\Util;

/**
 * S3 adapter that better supports directories and buckets.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class S3 extends AwsS3Adapter
{
    /**
     * @inheritdoc
     *
     * Fix to handle bucket for empty paths and directories.
     */
    public function getMetadata($path)
    {
        $dirResult = [
            'type'      => 'dir',
            'path'      => $path,
            'timestamp' => 0,
        ];

        $location = $this->applyPathPrefix($path);

        if (!$location) {
            $command = $this->s3Client->getCommand(
                'headBucket',
                [
                    'Bucket' => $this->bucket,
                ]
            );
        } else {
            $command = $this->s3Client->getCommand(
                'headObject',
                [
                    'Bucket' => $this->bucket,
                    'Key' => $location,
                ]
            );
        }

        /* @var Result $result */
        try {
            $result = $this->s3Client->execute($command);
        } catch (S3Exception $exception) {
            $response = $exception->getResponse();

            if ($response !== null && $response->getStatusCode() === 404) {

                // Check if directory exists, if so return basically nothing.
                if ($this->doesDirectoryExist($location)) {
                    return $dirResult;
                } else {
                    return false;
                }
            }

            throw $exception;
        }

        if (!$location) {
            return $dirResult;
        }

        return $this->normalizeResponse($result->toArray(), $path);
    }

    /**
     * @inheritdoc
     *
     * Fix to check if bucket existence for empty paths.
     */
    public function has($path)
    {
        $location = $this->applyPathPrefix($path);

        if (!$location) {
            return $this->s3Client->doesBucketExist($this->bucket);
        }

        if ($this->s3Client->doesObjectExist($this->bucket, $location)) {
            return true;
        }

        return $this->doesDirectoryExist($location);
    }

    /**
     * @inheritdoc
     *
     * Fix to use "getBucketAcl" if path is empty.
     * Also to check if location is directory, and if so
     * return "public" since directories don't have ACL.
     */
    protected function getRawVisibility($path)
    {
        $location = $this->applyPathPrefix($path);
        if ($location) {
            $command = $this->s3Client->getCommand(
                'getObjectAcl',
                [
                    'Bucket' => $this->bucket,
                    'Key' => $location,
                ]
            );
        } else {
            $command = $this->s3Client->getCommand(
                'getBucketAcl',
                [
                    'Bucket' => $this->bucket,
                ]
            );
        }

        try {
            $result = $this->s3Client->execute($command);
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 404) {
                // Dirs don't have ACL, just say public.
                if ($this->doesDirectoryExist($location)) {
                    return AdapterInterface::VISIBILITY_PUBLIC;
                }
            }

            throw new IOException("Failed to get file's visibility", $path, 0, $e);
        }

        $visibility = AdapterInterface::VISIBILITY_PRIVATE;

        foreach ($result->get('Grants') as $grant) {
            if (
                isset($grant['Grantee']['URI'])
                && $grant['Grantee']['URI'] === self::PUBLIC_GRANT_URI
                && $grant['Permission'] === 'READ'
            ) {
                $visibility = AdapterInterface::VISIBILITY_PUBLIC;
                break;
            }
        }

        return $visibility;
    }

    /**
     * @inheritdoc
     *
     * Fix to return empty string if path and pathPrefix are the same.
     */
    public function removePathPrefix($path)
    {
        $pathPrefix = $this->getPathPrefix();

        if ($pathPrefix === null) {
            return $path;
        }

        if ($path === $pathPrefix) {
            return '';
        }

        return substr($path, strlen($pathPrefix));
    }

    /**
     * @inheritdoc
     *
     * Only call Util::getStreamSize if $body is a resource.
     * Fixed prefix not being removed from response.
     */
    protected function upload($path, $body, Config $config)
    {
        $key = $this->applyPathPrefix($path);
        $options = $this->getOptionsFromConfig($config);
        $acl = isset($options['ACL']) ? $options['ACL'] : 'private';

        if ( ! isset($options['ContentType']) && is_string($body)) {
            $options['ContentType'] = Util::guessMimeType($path, $body);
        }

        if ( ! isset($options['ContentLength'])) {
            $options['ContentLength'] = is_string($body) ? Util::contentSize($body) : is_resource($body) ? Util::getStreamSize($body) : null;
        }

        if ($options['ContentLength'] === null) {
            unset($options['ContentLength']);
        }

        $this->s3Client->upload($this->bucket, $key, $body, $acl, ['params' => $options]);

        return $this->normalizeResponse($options, $path);
    }
}
