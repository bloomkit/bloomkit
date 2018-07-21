<?php
namespace Bloomkit\Core\Http\Session\Storage;

use Bloomkit\Core\Utilities\Repository;

class NativeSessionStorage implements SessionStorageInterface
{    
    /**
     * @var Repository
     */
    private $sessionData;
    
    /**
     * Constructor
     * 
     * @param mixed $handler SessionHandler
     */
    public function __construct($handler = null)
    {
        $this->sessionData = new Repository();
        
        session_cache_limiter('');
        ini_set('session.use_cookies', 1);    
        session_register_shutdown();
        $this->setSaveHandler($handler);
    }
    
    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->sessionData->clear();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->saveHandler->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->saveHandler->getName();
    }
    
    /**
     * Returns the SessionData object
     * 
     * @return Repository SessionData object
     */
    public function getSessionData()
    {
        return $this->sessionData();
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->saveHandler->setId($id);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->saveHandler->setName($name);
    }    
    
    /**
     * Set the SaveHandler
     * 
     * @param mixed $saveHandler The SaveHandler to use
     */
    public function setSaveHandler($saveHandler = null)
    {
        if (!is_null($saveHandler) && !$saveHandler instanceof \SessionHandlerInterface) {
            throw new \InvalidArgumentException('Invalid handler provided');
        }
    
        if (is_null($saveHandler))
            $this->saveHandler = new SessionHandlerProxy(new \SessionHandler());

        if ($this->saveHandler instanceof \SessionHandlerInterface) {
            session_set_save_handler($this->saveHandler, false);
        }
    }
}
