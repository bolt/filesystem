<?php

namespace Bolt\Filesystem;

interface PluginInterface
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set the Filesystem object.
     *
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem);
}
