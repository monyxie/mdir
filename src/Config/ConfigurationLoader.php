<?php


namespace Monyxie\Mdir\Config;


use Symfony\Component\Config\Loader\FileLoader;

class ConfigurationLoader extends FileLoader
{

    /**
     * Loads a resource.
     *
     * @param mixed $file The resource
     * @param string $type
     *
     * @return mixed
     * @throws \Exception If something went wrong
     */
    public function load($file, string $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        $config = require $path;

        return $config;
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string $type
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, string $type = null)
    {
        return \is_string($resource) && \in_array(pathinfo($resource, PATHINFO_EXTENSION), ['php'], true) && (!$type || 'php' === $type);
    }
}