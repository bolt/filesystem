<?php

namespace Bolt\Filesystem\Tests;

use Symfony\Component\Filesystem as Symfony;

abstract class FilesystemTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var string project root */
    protected $rootDir;
    /** @var string */
    protected $tempDir;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->rootDir = __DIR__ . '/..';
        $this->tempDir = $this->rootDir . '/tests/temp';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->removeDirectory($this->tempDir);
    }

    protected function removeDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $fs = new Symfony\Filesystem();
        $fs->remove($dir);
    }
}
