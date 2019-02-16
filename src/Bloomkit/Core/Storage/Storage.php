<?php

namespace Bloomkit\Core\Storage;

use InvalidArgumentException;
use Bloomkit\Core\Storage\Adapter\StorageAdapterInterface;
use Bloomkit\Core\Storage\Utils\Utils;
use Bloomkit\Core\Storage\Exceptions\FileNotFoundException;
use Bloomkit\Core\Storage\Exceptions\FileExistsException;

class Storage implements StorageInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     * @param Config|array     $config
     */
    public function __construct(StorageAdapterInterface $adapter, $config = null)
    {
        $this->adapter = $adapter;

        if ($config === null) {
            $this->config = new Config();
        } elseif ($config instanceof Config) {
            $this->config = $config;
        } elseif (is_array($config)) {
            return new Config($config);
        } else {
            throw new \LogicException('invalid config');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $path = Utils::normalizePath($path);
        $newpath = Utils::normalizePath($newpath);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        if ($this->has($newpath)) {
            throw new FileExistsException();
        }
        return $this->getAdapter()->copy($path, $newpath);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, array $config = [])
    {
        $dirname = Utils::normalizePath($dirname);
        $config = $this->prepareConfig($config);

        return (bool) $this->getAdapter()->createDir($dirname, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        return $this->getAdapter()->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        $dirname = Utils::normalizePath($dirname);
        if ($dirname === '') {
            throw new RootViolationException('Root directories can not be deleted.');
        }

        return (bool) $this->getAdapter()->deleteDir($dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, Handler $handler = null)
    {
        $path = Utils::normalizePath($path);
        if (!$handler) {
            $metadata = $this->getMetadata($path);
            $handler = $metadata['type'] === 'file' ? new File($this, $path) : new Directory($this, $path);
        }
        $handler->setPath($path);
        $handler->setFilesystem($this);

        return $handler;
    }

    /**
     * Get the Adapter.
     *
     * @return AdapterInterface adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Get the Config.
     *
     * @return Config config object
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        return $this->getAdapter()->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        if ((!$object = $this->getAdapter()->getMimetype($path)) || !array_key_exists('mimetype', $object)) {
            return false;
        }

        return $object['mimetype'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        if ((!$object = $this->getAdapter()->getSize($path)) || !array_key_exists('size', $object)) {
            return false;
        }

        return (int) $object['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        if ((!$object = $this->getAdapter()->getTimestamp($path)) || !array_key_exists('timestamp', $object)) {
            return false;
        }

        return $object['timestamp'];
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        if ((!$object = $this->getAdapter()->getVisibility($path)) || !array_key_exists('visibility', $object)) {
            return false;
        }

        return $object['visibility'];
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $path = Utils::normalizePath($path);

        return strlen($path) === 0 ? false : (bool) $this->getAdapter()->has($path);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        $directory = Utils::normalizePath($directory);
        $contents = $this->getAdapter()->listContents($directory, $recursive);

        return $contents;
    }

    /**
     * Convert a config array to a Config object with the correct fallback.
     *
     * @param array $config
     *
     * @return Config
     */
    protected function prepareConfig(array $config)
    {
        $config = new Config($config);
        $config->setFallback($this->getConfig());

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, array $config = [])
    {
        $path = Utils::normalizePath($path);
        $config = $this->prepareConfig($config);
        if (!$this->getAdapter() instanceof CanOverwriteFiles && $this->has($path)) {
            return (bool) $this->getAdapter()->update($path, $contents, $config);
        }

        return (bool) $this->getAdapter()->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($path, $resource, array $config = [])
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException(__METHOD__.' expects argument #2 to be a valid resource.');
        }
        $path = Utils::normalizePath($path);
        $config = $this->prepareConfig($config);
        Utils::rewindStream($resource);
        if (!$this->getAdapter() instanceof CanOverwriteFiles && $this->has($path)) {
            return (bool) $this->getAdapter()->updateStream($path, $resource, $config);
        }

        return (bool) $this->getAdapter()->writeStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        if (!($object = $this->getAdapter()->read($path))) {
            return false;
        }

        return $object['contents'];
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        if (!$object = $this->getAdapter()->readStream($path)) {
            return false;
        }

        return $object['stream'];
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        $path = Utils::normalizePath($path);
        $newpath = Utils::normalizePath($newpath);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        if ($this->has($newpath)) {
            throw new FileExistsException();
        }
        return (bool) $this->getAdapter()->rename($path, $newpath);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        $path = Utils::normalizePath($path);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        return (bool) $this->getAdapter()->setVisibility($path, $visibility);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, array $config = [])
    {
        $path = Utils::normalizePath($path);
        $config = $this->prepareConfig($config);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        return (bool) $this->getAdapter()->update($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, array $config = [])
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException(__METHOD__.' expects argument #2 to be a valid resource.');
        }
        $path = Utils::normalizePath($path);
        $config = $this->prepareConfig($config);
        if (!$this->has($path)) {
            throw new FileNotFoundException();
        }
        Utils::rewindStream($resource);

        return (bool) $this->getAdapter()->updateStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, array $config = [])
    {
        $path = Utils::normalizePath($path);
        if ($this->has($path)) {
            throw new FileExistsException();
        }
        $config = $this->prepareConfig($config);

        return (bool) $this->getAdapter()->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, array $config = [])
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException(__METHOD__.' expects argument #2 to be a valid resource.');
        }
        $path = Utils::normalizePath($path);
        if ($this->has($path)) {
            throw new FileExistsException();
        }
        $config = $this->prepareConfig($config);
        Utils::rewindStream($resource);

        return (bool) $this->getAdapter()->writeStream($path, $resource, $config);
    }
}
