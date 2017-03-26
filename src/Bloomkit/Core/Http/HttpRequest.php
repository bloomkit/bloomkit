<?php
namespace Bloomkit\Core\Http;

use Bloomkit\Core\Utilities\Repository;
use Bloomkit\Core\Http\Exceptions\SuspiciousOperationException;

class HttpRequest
{
    /**
     * @var Repository
     */
    protected $cookies;
    
    /**
     * @var Repository
     */
    protected $files;
    
    /**
     * @var Repository
     */
    protected $getParams;
    
    /**
     * @var Repository
     */
    protected $headers;
    
    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @var Repository
     */
    protected $postParams;
    
    /**
     * @var string
     */
    protected $requestUri;
        
    /**
     * @var Repository
     */
    protected $serverParams;
        
    /**
     * @var SessionInterface
     */
    protected $session;
    
    /**
     * Constructor
     *
     * @param array $server     SERVER Parameter ($_SERVER)
     * @param array $get        GET Parameter ($_GET)
     * @param array $post       POST Parameter ($_POST)
     * @param array $cookies    COOKIE Parameter ($_COOKIE)
     * @param array $files      FILES Parameter ($_FILES)
     */
    public function __construct(array $server = [], array $get = [], array $post = [], array $cookies = [], array $files = [])
    {
        $this->getParams = new Repository($get);
        $this->postParams = new Repository($post);
        $this->cookies = new Repository($cookies);
        $this->files = new Repository($files);
        $this->serverParams = new Repository($server);
        
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0)
                $headers[substr($key, 5)] = $value;
            else if (strpos($key, 'CONTENT_') === 0)
                $headers[$key] = $value;
        }
        $this->headers = new Repository($headers);        
    }
    
    /**
     * Returns the client ip
     *
     * @return string
     */
    public function getClientIp()
    {
        return $this->serverParams->get('REMOTE_ADDR');
    }

    /**
     * Returns the request cookies
     *
     * @return Repository
     */
    public function getCookies()
    {
        return $this->cookies;
    }
    
    /**
     * Returns the FILES parameters
     *
     * @return Repository
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    /**
     * Returns the GET parameters
     *
     * @return Repository
     */
    public function getGetParams()
    {
        return $this->getParams;
    }
    
    /**
     * Returns the HTTP-headers
     *
     * @return Repository
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Returns the Host being requested (host + maybe port, no scheme)
     *
     * @param bool $forcePort
     *
     * @return string
     */
    public function getHost($forcePort = FALSE)
    {              
        $host = $this->serverParams->get('SERVER_NAME', '');
        if ($host == '')
            $host = $this->serverParams->get('SERVER_ADDR', '');
        
        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
        
        // as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
        // check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
        // use preg_replace() instead of preg_match() to prevent DoS attacks with long host names
        if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
            throw new SuspiciousOperationException(sprintf('Invalid Host "%s".', $host));
        }
        
        $port  = $this->getPort();
        $https = strtolower($this->serverParams->get('HTTPS',''));

        //If the default port is used an forcedPort = false return only the host without port
        if (!isset($port)||((!$forcePort) && (($https == 'on' && $port == 443) || ($https !== 'on' && $port == 80))))
            return $host;
        
        return $host.':'.$port;
    }

    /**
     * Returns the HTTP-Method (GET, POST, etc)
     *
     * @return string
     */
    public function getHttpMethod()
    {
        if (is_null($this->httpMethod))
            $this->httpMethod = strtoupper($this->serverParams->get('REQUEST_METHOD', 'GET'));
        return $this->httpMethod;
    }
    
    /**
     * Returns the server port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->serverParams->get('SERVER_PORT');
    }
    
    /**
     * Returns the POST parameters
     *
     * @return Repository
     */
    public function getPostParams()
    {
        return $this->postParams;
    }
    
    /**
     * Returns the requestUri
     *
     * @return HttpRequest
     */
    public function getRequestUri()
    {
        if (is_null($this->requestUri))
        {
            $this->normalizeRequestUri();
            $this->requestUri = $this->serverParams->get('REQUEST_URI');
        }
        return $this->requestUri;
    }
    
    /**
     * Returns the scheme (https/http)
     *
     * @return string
     */
    public function getScheme()
    {
        if ((strtolower($this->serverParams->get('HTTPS','')) == 'on') || ( $this->getPort() == '443'))
            return 'https';
        else
            return 'http';
    }
   
    /**
     * Returns the SERVER parameters
     *
     * @return Repository
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }
    
    /**
     * Returns the Session
     *
     * @return SessionInterface|null The session
     */
    public function getSession()
    {
        return $this->session;
    }    

    /**
     * Check if a session is set
     *
     * @return boolean
     */
    public function hasSession()
    {
        return isset($this->session);
    }
    
    /**
     * Normalize the requestUri by handling server-specific Http-headers
     */
    protected function normalizeRequestUri()
    {
        $requestUri = '';        
        if ($this->headers->has('X_ORIGINAL_URL')) {
            // IIS with Microsoft Rewrite
            $requestUri = $this->headers->get('X_ORIGINAL_URL');
            $this->headers->remove('X_ORIGINAL_URL');
            $this->serverParams->remove('HTTP_X_ORIGINAL_URL');
            $this->serverParams->remove('UNENCODED_URL');
            $this->serverParams->remove('IIS_WasUrlRewritten');
        } elseif ($this->headers->has('X_REWRITE_URL')) {
            // IIS with ISAPI_Rewrite
            $requestUri = $this->headers->get('X_REWRITE_URL');
            $this->headers->remove('X_REWRITE_URL');
        } elseif ($this->serverParams->get('IIS_WasUrlRewritten') == '1' && $this->serverParams->get('UNENCODED_URL','') != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = $this->serverParams->get('UNENCODED_URL');
            $this->serverParams->remove('UNENCODED_URL');
            $this->serverParams->remove('IIS_WasUrlRewritten');
        } elseif ($this->serverParams->has('ORIG_PATH_INFO')) {
            // IIS5 with PHP as CGI
            $requestUri = $this->serverParams->get('ORIG_PATH_INFO');
            $qryStr = $this->serverParams->get('QUERY_STRING','');
            if ($qryStr != '')
                $requestUri .= '?' . $qryStr;
            $this->serverParams->remove('ORIG_PATH_INFO');
        } 
        if ($this->serverParams->has('REQUEST_URI')) {
            $requestUri = $this->serverParams->get('REQUEST_URI');
            $prefix = $this->getScheme().'://'.$this->getHost();
            if (strpos($requestUri, $prefix) === 0)
                $requestUri = substr($requestUri, strlen($prefix));
        }
        $this->serverParams->set('REQUEST_URI', $requestUri);
    }
    
    /**
     * Create and return a HttpRequest from php superglobals
     *
     * @return HttpRequest
     */
    public static function processRequest()
    {
        return new HttpRequest($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    }
    
    /**
     * Set a session
     *
     * @param SessionInterface $session The Session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }
} 