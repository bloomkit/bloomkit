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
     * @var boolean
     */
    private $isClosed;
    
    /**
     * @var boolean
     */
    private $isStarted;
    
    /**
     * @var string;
     */
    private $storageKey;
        
    /**
     * Constructor
     * 
     * @param mixed $handler SessionHandler
     * @param string $storageKey Key for saving session data
     */
    public function __construct($handler = null, $storageKey = '_bk_session_data')
    {
        $this->sessionData = new Repository();
        $this->storageKey = $storageKey;
        
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
        return $this->sessionData;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIsStarted()
    {
        return $this->isStarted;
    }

    /**
     * Load the session 
     *
     * @param array|null $session
     */
    protected function loadSession(array &$session = null)
    {
        if (is_null($session))
            $session = &$_SESSION;
        
        if(isset($session[$this->storageKey]))
            $this->sessionData->addItems($session[$this->storageKey]);
        
        $this->isStarted = true;
        $this->isClosed = false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function save()
    {
        session_write_close();    
        $this->isClosed = true;
        $this->isStarted = false;
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
    
    /**
     * {@inheritdoc}
     */
    public function start()
    {
        if ($this->isStarted && ! $this->isClosed) {
            return true;
        }
    
        if (session_status() === \PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session already started.');
        }
        
        if (ini_get('session.use_cookies') && headers_sent($file, $line)) {
            throw new \RuntimeException(sprintf('Failed to start the session: Headers have already been sent by "%s" at line %d.', $file, $line));
        }
    
        if (! session_start()) {
            throw new \RuntimeException('Failed to start the session');
        }
    
        $this->loadSession();    
        return true;
    }
}
