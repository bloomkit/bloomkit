<?php
namespace Bloomkit\Core\Http\Session\Storage;

class SessionHandlerProxy implements \SessionHandlerInterface
{   
    /**
     *
     * @var bool
     */
    protected $active = false;
    
    /**
     *
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * Constructor.
     *
     * @param \SessionHandlerInterface $handler
     */
    public function __construct(\SessionHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->active = false;
        return (bool) $this->handler->close();
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        return (bool) $this->handler->destroy($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return (bool) $this->handler->gc($maxlifetime);
    }
    
    /**
     * Returns the session ID.
     *
     * @return string The session ID
     */
    public function getId()
    {
        return session_id();
    }
    
    /**
     * Returns the session name.
     *
     * @return string The session name
     */
    public function getName()
    {
        return session_name();
    }
    
    /**
     * Is the session started
     *
     * @return bool True if session is started false if not
     */
    public function isActive()
    {
        $this->active = session_status() === \PHP_SESSION_ACTIVE;
        return $this->active;
    }    
    
    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        $this->active = (bool) $this->handler->open($savePath, $sessionName);
        return $this->active;
    }    
    
    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return (string) $this->handler->read($sessionId);
    }
    
    /**
     * Sets the session ID.
     *
     * @param string $id The ID to set
     */
    public function setId($id)
    {
        if ($this->isActive())
            throw new \LogicException('Cannot change the ID of an active session');
        session_id($id);
    }
    
    /**
     * Sets the session name.
     *
     * @param string $name The name to set
     */
    public function setName($name)
    {
        if ($this->isActive())
            throw new \LogicException('Cannot change the name of an active session');
        session_name($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        return (bool) $this->handler->write($sessionId, $data);
    }
}
