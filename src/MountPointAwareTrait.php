<?php

namespace Bolt\Filesystem;

/**
 * @author Carson Full <carsonfull@gmail.com>
 */
trait MountPointAwareTrait
{
    /** @var string|null */
    protected $mountPoint;

    /**
     * Returns the aggregate filesystem's mount point.
     *
     * @return string|null
     */
    public function getMountPoint()
    {
        return $this->mountPoint;
    }

    /**
     * WARNING: Do not call this unless you know what you are doing.
     *
     * @param string $mountPoint
     */
    public function setMountPoint($mountPoint)
    {
        $this->mountPoint = $mountPoint;
    }
}
