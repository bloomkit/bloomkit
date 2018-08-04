<?php

namespace Bloomkit\Core\Http\Session;

use Bloomkit\Core\Http\Session\Storage\SessionStorageInterface;
use Bloomkit\Core\Http\Session\Storage\NativeSessionStorage;

class Session implements SessionInterface
{
    /**
     * @var SessionStorageInterface
     */
    protected $storage;

    /**
     * Constructor.
     *
     * @param SessionStorageInterface $storage Storage object to save session data
     */
    public function __construct(SessionStorageInterface $storage = null)
    {
        if (is_null($storage)) {
            $storage = new NativeSessionStorage();
        }

        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->storage->getSessionData()->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->storage->getSessionData()->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        return $this->storage->getSessionData()->get($name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->storage->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->storage->getSessionMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->storage->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->storage->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->storage->setId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        return $this->storage->start();
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->storage->save();
    }
}
