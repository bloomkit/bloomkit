<?php
namespace Bloomkit\Core\Http\Session\Storage;

use Bloomkit\Core\Http\Session\SessionRepository;

class MockSessionStorage implements SessionStorageInterface
{
    /**
     * @var SessionRepository
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
    private $name;
    
    /**
     * @var string;
     */
    private $id;

    /**
     * Constructor
     *
     * @param string $name The name of the session
     */
    public function __construct($name = 'MOCKSESSID')
    {
        $this->name = $name;
        $this->sessionData = new SessionRepository();
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
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Generates a session ID (just a mock)
     *
     * @return string
     */
    protected function generateId()
    {
        return hash('sha256', uniqid('bk_mock_', true));
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
     */
    protected function loadSession()
    {
        $this->isStarted = true;
        $this->isClosed = false;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        if (!$this->started || $this->closed) {
            throw new \RuntimeException('Trying to save a session that was not started or was already closed');
        }
        $this->closed = false;
        $this->started = false;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        if ($this->started) {
            throw new \LogicException('Cannot set session ID after the session has started.');
        }
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        if ($this->isStarted && !$this->isClosed) {
            return true;
        }
        
        if (empty($this->id)) {
            $this->id = $this->generateId();
        }
        
        $this->loadSession();
        return true;
    }
}