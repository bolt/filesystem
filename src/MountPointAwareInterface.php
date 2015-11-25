<?php

namespace Bolt\Filesystem;

/**
 * @author Carson Full <carsonfull@gmail.com>
 */
interface MountPointAwareInterface
{
    /**
     * Returns the aggregate filesystem's mount point.
     *
     * @return string|null
     */
    public function getMountPoint();

    /**
     * WARNING: Do not call this unless you know what you are doing.
     *
     * @param string $mountPoint
     */
    public function setMountPoint($mountPoint);
}
