<?php

namespace Bloomkit\Core\Http;

use Bloomkit\Core\Utilities\Repository;
use Bloomkit\Core\Http\Exceptions\SuspiciousOperationException;
use Bloomkit\Core\Http\Session\SessionInterface;

/**
 * Representation of a http request.
 */
class HttpRequest
{
    /**
     * @var Repository
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $baseUrl;

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
     * @var array
     */
    protected $languages;

    /**
     * @var string
     */
    protected $pathUrl;

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
     * Constructor.
     *
     * @param array $server  SERVER Parameter ($_SERVER)
     * @param array $get     GET Parameter ($_GET)
     * @param array $post    POST Parameter ($_POST)
     * @param array $cookies COOKIE Parameter ($_COOKIE)
     * @param array $files   FILES Parameter ($_FILES)
     */
    public function __construct(array $server = [], array $get = [], array $post = [], array $cookies = [], array $files = [])
    {
        $this->getParams = new Repository($get);
        $this->postParams = new Repository($post);
        $this->cookies = new Repository($cookies);
        $this->files = new Repository($files);
        $this->serverParams = new Repository($server);
        $this->attributes = new Repository();

        $headers = [];
        foreach ($server as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (0 === strpos($key, 'CONTENT_')) {
                $headers[$key] = $value;
            }
        }
        $this->headers = new Repository($headers);
    }

    /**
     * Returns the request attributes.
     *
     * @return Repository
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns the root URL from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    /**
     * Returns the client ip.
     *
     * @return string
     */
    public function getClientIp()
    {
        return $this->serverParams->get('REMOTE_ADDR');
    }

    /**
     * Returns the request cookies.
     *
     * @return Repository
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Returns the FILES parameters.
     *
     * @return Repository
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Returns the full Request-URL withoud url parameters - e.g. http://localhost:8081/mysite.
     *
     * @return string Request url
     */
    public function getFullUrl($forcePorts = false)
    {
        $serverPort = $this->serverParams->get('SERVER_PORT', '80');
        $serverName = $this->serverParams->get('SERVER_NAME', '');
        $requestUri = $this->serverParams->get('REQUEST_URI', '');
        $urlParts = parse_url($requestUri);
        $requestUri = $urlParts['path'];

        if (($this->serverParams->get('HTTPS', null) == 'on') || ($serverPort == '443')) {
            $pageUrl = 'https://';
        } else {
            $pageUrl = 'http://';
        }

        // Ignore default ports if forcePorts != true
        if (($forcePorts == true) || (($serverPort != '80') && ($serverPort != '443'))) {
            $pageUrl .= $serverName.':'.$serverPort.$requestUri;
        } else {
            $pageUrl .= $serverName.$requestUri;
        }

        return $pageUrl;
    }

    /**
     * Returns the GET parameters.
     *
     * @return Repository
     */
    public function getGetParams()
    {
        return $this->getParams;
    }

    /**
     * Returns the HTTP-headers.
     *
     * @return Repository
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the Host being requested (host + maybe port, no scheme).
     *
     * @param bool $forcePort
     *
     * @return string
     */
    public function getHost($forcePort = false)
    {
        $host = $this->serverParams->get('SERVER_NAME', '');
        if ('' == $host) {
            $host = $this->serverParams->get('SERVER_ADDR', '');
        }

        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        // as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
        // check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
        // use preg_replace() instead of preg_match() to prevent DoS attacks with long host names
        if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
            throw new SuspiciousOperationException(sprintf('Invalid Host "%s".', $host));
        }

        $port = $this->getPort();
        $https = strtolower($this->serverParams->get('HTTPS', ''));

        //If the default port is used an forcedPort = false return only the host without port
        if (!isset($port) || ((!$forcePort) && (('on' == $https && 443 == $port) || ('on' !== $https && 80 == $port)))) {
            return $host;
        }

        return $host.':'.$port;
    }

    /**
     * Returns the HTTP-Method (GET, POST, etc).
     *
     * @return string
     */
    public function getHttpMethod()
    {
        if (is_null($this->httpMethod)) {
            $this->httpMethod = strtoupper($this->serverParams->get('REQUEST_METHOD', 'GET'));
        }

        return $this->httpMethod;
    }

    /**
     * Gets a list of languages accepted by the client browser.
     *
     * @return array List of languages
     */
    public function getLanguages()
    {
        if (!is_null($this->languages)) {
            return $this->languages;
        }

        $languages = [];
        $value = trim($this->getHeaders()->get('ACCEPT_LANGUAGE', ''));
        $values = explode(',', $value);
        foreach ($values as $tmpVal) {
            $items = explode(';', $tmpVal);
            $languages[] = $items[0];
        }

        $this->languages = $languages;

        return $this->languages;
    }

    /**
     * Returns the QUERY_STRING Parameter.
     *
     * @return string
     */
    public function getParamStr()
    {
        return $this->getServerParams()->get('QUERY_STRING', '');
    }

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getPathUrl()
    {
        if (null === $this->pathUrl) {
            $this->pathUrl = $this->preparePathUrl();
        }

        return $this->pathUrl;
    }

    /**
     * Returns the server port.
     *
     * @return string
     */
    public function getPort()
    {
        return $this->serverParams->get('SERVER_PORT');
    }

    /**
     * Returns the POST parameters.
     *
     * @return Repository
     */
    public function getPostParams()
    {
        return $this->postParams;
    }

    /**
     * Returns the requestUri.
     *
     * @return HttpRequest
     */
    public function getRequestUri()
    {
        if (is_null($this->requestUri)) {
            $this->normalizeRequestUri();
            $this->requestUri = $this->serverParams->get('REQUEST_URI');
        }

        return $this->requestUri;
    }

    /**
     * Returns the scheme (https/http).
     *
     * @return string
     */
    public function getScheme()
    {
        if (('on' == strtolower($this->serverParams->get('HTTPS', ''))) || ('443' == $this->getPort())) {
            return 'https';
        } else {
            return 'http';
        }
    }

    /**
     * Returns the SERVER parameters.
     *
     * @return Repository
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Returns the Session.
     *
     * @return SessionInterface|null The session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, false otherwise.
     *
     * @param string $string The urlencoded string
     * @param string $prefix The prefix not encoded
     *
     * @return string|false The prefix as it is encoded in $string, or false
     */
    private function getUrlencodedPrefix($string, $prefix)
    {
        if (empty($prefix)) {
            return false;
        }

        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return false;
        }

        $len = strlen($prefix);
        if (preg_match("#^(%[[:xdigit:]]{2}|.){{$len}}#", $string, $match)) {
            return $match[0];
        }

        return false;
    }

    /**
     * Check if a session is set.
     *
     * @return bool
     */
    public function hasSession()
    {
        return isset($this->session);
    }

    /**
     * Check if request is made by https.
     *
     * @return bool
     */
    public function isSecure()
    {
        $https = $this->getServerParams()->get('HTTPS');

        return !empty($https) && 'off' !== strtolower($https);
    }

    /**
     * Normalize the requestUri by handling server-specific Http-headers.
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
        } elseif ('1' == $this->serverParams->get('IIS_WasUrlRewritten') && '' != $this->serverParams->get('UNENCODED_URL', '')) {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = $this->serverParams->get('UNENCODED_URL');
            $this->serverParams->remove('UNENCODED_URL');
            $this->serverParams->remove('IIS_WasUrlRewritten');
        } elseif ($this->serverParams->has('ORIG_PATH_INFO')) {
            // IIS5 with PHP as CGI
            $requestUri = $this->serverParams->get('ORIG_PATH_INFO');
            $qryStr = $this->serverParams->get('QUERY_STRING', '');
            if ('' != $qryStr) {
                $requestUri .= '?'.$qryStr;
            }
            $this->serverParams->remove('ORIG_PATH_INFO');
        }
        if ($this->serverParams->has('REQUEST_URI')) {
            $requestUri = $this->serverParams->get('REQUEST_URI');
            $prefix = $this->getScheme().'://'.$this->getHost();
            if (0 === strpos($requestUri, $prefix)) {
                $requestUri = substr($requestUri, strlen($prefix));
            }
        }
        $this->serverParams->set('REQUEST_URI', $requestUri);
    }

    /**
     * Prepares the base url.
     *
     * @return string
     */
    protected function prepareBaseUrl()
    {
        $filename = basename($this->serverParams->get('SCRIPT_FILENAME'));

        if (basename($this->serverParams->get('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->serverParams->get('SCRIPT_NAME');
        } elseif (basename($this->serverParams->get('PHP_SELF')) === $filename) {
            $baseUrl = $this->serverParams->get('PHP_SELF');
        } elseif (basename($this->serverParams->get('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->serverParams->get('ORIG_SCRIPT_NAME');
        } else {
            $path = $this->serverParams->get('PHP_SELF', '');
            $file = $this->serverParams->get('SCRIPT_FILENAME', '');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();
        if (empty($requestUri)) {
            return '';
        }

        $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl);

        if ($baseUrl && false !== $prefix) {
            // full $baseUrl matches
            return $prefix;
        }

        $prefix = $this->getUrlencodedPrefix($requestUri, dirname($baseUrl));
        if ($baseUrl && false !== $prefix) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/');
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }

    /**
     * Prepares the path url.
     *
     * @return string
     */
    protected function preparePathUrl()
    {
        // Find base-url (e.g: /myapp/index.php)
        $baseUrl = $this->getBaseUrl();

        // Find request-URI (e.g: /myapp/index.php/test/a?param=value)
        $requestUri = $this->getRequestUri();

        if (is_null($requestUri)) {
            return '/';
        }

        // remove url params from request-URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if (is_null($baseUrl)) {
            return $requestUri;
        }

        $pathInfo = substr($requestUri, strlen($baseUrl));

        if (false === $pathInfo) {
            return '/';
        } else {
            return $pathInfo;
        }
    }

    /**
     * Create and return a HttpRequest from php superglobals.
     *
     * @return HttpRequest
     */
    public static function processRequest()
    {
        return new HttpRequest($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    }

    /**
     * Set a session.
     *
     * @param SessionInterface $session The Session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }
}
