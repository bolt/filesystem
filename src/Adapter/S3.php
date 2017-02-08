<?php

namespace Bolt\Filesystem\Adapter;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
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

        if ($location === '') {
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
                /*
                 * Path could be a directory. If so, return enough info to treat it as a directory.
                 * We should really never get here and the path not be a directory, since existence
                 * has already been verified.
                 */
                if ($this->doesDirectoryExist($location)) {
                    return $dirResult;
                }
            }

            throw $exception;
        }

        /*
         * Root paths may not throw an exception because:
         * - headBucket() always has a valid response (since existence has already been verified).
         * - Applying prefix to empty path will result in a trailing slash. If an exception is not
         *   thrown for this object it is a fake directory (see createDir()).
         *
         * Both of these cases mean the path is a directory. We return that here,
         * because empty paths aren't handled correctly by normalizeResponse.
         */
        if ($path === '') {
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

        if ($location === '') {
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
        if ($location === '') {
            $command = $this->s3Client->getCommand(
                'getBucketAcl',
                [
                    'Bucket' => $this->bucket,
                ]
            );
        } else {
            $command = $this->s3Client->getCommand(
                'getObjectAcl',
                [
                    'Bucket' => $this->bucket,
                    'Key' => $location,
                ]
            );
        }

        try {
            $result = $this->s3Client->execute($command);
        } catch (S3Exception $e) {
            $response = $e->getResponse();

            if ($response !== null && $response->getStatusCode() === 404) {
                /*
                 * Path could be a directory. If so, return "public" since directories don't have ACL.
                 * We should really never get here and the path not be a directory, since existence
                 * has already been verified.
                 */
                if ($this->doesDirectoryExist($location)) {
                    return AdapterInterface::VISIBILITY_PUBLIC;
                }
            }

            throw $e;
        }

        /*
         * See note in getMetadata().
         *
         * TODO We say buckets are always public since we treat them like directories, which we say are public.
         * But buckets actually have visibility. Should we use that instead of assuming it is public?
         */
        if ($path === '') {
            return AdapterInterface::VISIBILITY_PUBLIC;
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
     * Guess mime type even when body is a resource.
     */
    protected function upload($path, $body, Config $config)
    {
        $key = $this->applyPathPrefix($path);
        $options = $this->getOptionsFromConfig($config);
        $acl = isset($options['ACL']) ? $options['ACL'] : 'private';

        if ( ! isset($options['ContentType'])) {
            $options['ContentType'] = Util::guessMimeType($path, $body);
        }

        if ( ! isset($options['ContentLength'])) {
            $options['ContentLength'] = is_string($body) ? Util::contentSize($body) : (is_resource($body) ? Util::getStreamSize($body) : null);
        }

        if ($options['ContentLength'] === null) {
            unset($options['ContentLength']);
        }

        $this->s3Client->upload($this->bucket, $key, $body, $acl, ['params' => $options]);

        return $this->normalizeResponse($options, $path);
    }
}
