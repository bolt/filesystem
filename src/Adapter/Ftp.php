<?php

namespace Bolt\Filesystem\Adapter;

use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Config;

/**
 * FTP adapter.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Ftp extends FtpAdapter
{
    /** @var int */
    protected $directoryPerm = 0744;

    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $this->configurable[] = 'directoryPerm';
        parent::__construct($config);
    }

    /**
     * @return int
     */
    public function getDirectoryPerm()
    {
        return $this->directoryPerm;
    }

    /**
     * @param int $directoryPerm
     *
     * @return Ftp
     */
    public function setDirectoryPerm($directoryPerm)
    {
        $this->directoryPerm = $directoryPerm;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createDir($dirname, Config $config)
    {
        if ($this->has($dirname)) {
            return true;
        }

        return parent::createDir($dirname, $config);
    }

    /**
     * {@inheritdoc}
     */
    protected function createActualDirectory($directory, $connection)
    {
        $result = parent::createActualDirectory($directory, $connection);
        if ($result) {
            ftp_chmod($connection, $this->directoryPerm, $directory);
        }

        return $result;
    }
}
