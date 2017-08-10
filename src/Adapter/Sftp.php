<?php

namespace Bolt\Filesystem\Adapter;

use League\Flysystem\Config;
use League\Flysystem\Sftp\SftpAdapter;

/**
 * SFTP adapter.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Sftp extends SftpAdapter
{
    /**
     * @inheritdoc
     */
    public function createDir($dirname, Config $config)
    {
        if ($this->has($dirname)) {
            return true;
        }

        $connection = $this->getConnection();
        if (!$connection->mkdir($dirname, $this->directoryPerm, true)) {
            return false;
        }
        // \phpseclib\Net\SFTP::mkdir() v2 fails to apply the correct
        // permissions on mkdir() when a umask of 022 is set, but chmod() still
        // works.
        $connection->chmod($this->directoryPerm, $dirname, true);

        return ['path' => $dirname];
    }
}
