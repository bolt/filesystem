<?php

namespace Bolt\Filesystem\Plugin;

use Bolt\Filesystem\Exception\BadMethodCallException;
use Bolt\Filesystem\Exception\InvalidArgumentException;
use Bolt\Filesystem\Exception\LogicException;
use Bolt\Filesystem\Exception\PluginNotFoundException;
use Bolt\Filesystem\FilesystemInterface;
use Bolt\Filesystem\PluginInterface;

trait PluggableTrait
{
    /** @var PluginInterface[] */
    protected $plugins = [];

    /**
     * Register a plugin.
     *
     * @param PluginInterface $plugin
     */
    public function addPlugin(PluginInterface $plugin)
    {
        $this->plugins[$plugin->getMethod()] = $plugin;
    }

    /**
     * Register a list of plugins.
     *
     * @param PluginInterface[] $plugins
     *
     * @throws InvalidArgumentException
     */
    public function addPlugins(array $plugins)
    {
        foreach ($plugins as $plugin) {
            if (!$plugin instanceof PluginInterface) {
                throw new InvalidArgumentException('Plugin must be instance of Bolt\Filesystem\PluginInterface');
            }
            $this->addPlugin($plugin);
        }
    }

    /**
     * Find a specific plugin.
     *
     * @param string $method
     *
     * @throws LogicException
     *
     * @return PluginInterface $plugin
     */
    protected function findPlugin($method)
    {
        if (!isset($this->plugins[$method])) {
            throw new PluginNotFoundException('Plugin not found for method: '.$method);
        }

        if (!method_exists($this->plugins[$method], 'handle')) {
            throw new LogicException(get_class($this->plugins[$method]).' does not have a handle method.');
        }

        return $this->plugins[$method];
    }

    /**
     * Invoke a plugin by method name.
     *
     * @param string              $method
     * @param array               $arguments
     * @param FilesystemInterface $filesystem
     *
     * @return mixed
     */
    protected function invokePlugin($method, array $arguments, FilesystemInterface $filesystem)
    {
        $plugin = $this->findPlugin($method);
        $plugin->setFilesystem($filesystem);
        $callback = [$plugin, 'handle'];

        return call_user_func_array($callback, $arguments);
    }

    /**
     * Plugins pass-through.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        try {
            return $this->invokePlugin($method, $arguments, $this);
        } catch (PluginNotFoundException $e) {
            throw new BadMethodCallException(
                'Call to undefined method '
                .get_class($this)
                .'::'.$method
            );
        }
    }
}
