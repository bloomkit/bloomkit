<?php

namespace Bloomkit\Core\Rest;

use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Rest\Exceptions\RestException;

/**
 * Representation of a rest request.
 */
class RestRequest extends HttpRequest
{
    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var array
     */
    private $jsonData;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $postData;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $restUrl;

    /**
     * Constructor.
     *
     * @param array  $server  SERVER Parameter ($_SERVER)
     * @param array  $get     GET Parameter ($_GET)
     * @param string $post    POST-data as string
     * @param array  $cookies COOKIE Parameter ($_COOKIE)
     * @param array  $files   FILES Parameter ($_FILES)
     * @param string $apiBase The first part of the api-url - dafault "/api/"
     */
    public function __construct(array $server = [], array $get = [], $post = '', array $cookies = [], array $files = [], $apiBase = 'api')
    {
        parent::__construct($server, $get, [], $cookies, $files);

        if ((isset($post)) && ('' != trim($post))) {
            $this->jsonData = json_decode($post, true);
            $this->postData = $post;
        } else {
            $this->jsonData = null;
            $this->postData = null;
        }

        $this->parseRequestUrl($this->getPathUrl(), $apiBase);
    }

    /**
     * Returns the requested api version.
     *
     * @return string The requested api version
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Returns the json data from the request.
     *
     * @return array The requests json-data
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * Returns the requested module name.
     *
     * @return string The requested module-name
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Returns the post-data string.
     *
     * @return string The post data from the request
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * Returns the prefix - the part of the url before the api-base e.g. "/myPrefix/api/v1/...".
     *
     * @return string The prefix of the request
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Returns the rest-url - the part of the url after the api-base and version.
     *
     * @return string The rest url
     */
    public function getRestUrl()
    {
        return $this->restUrl;
    }

    /**
     * Parses the request url and extract the required informations.
     *
     * @param string $requestUrl The request url to parse
     * @param string $apiBase    The first part of the api-url - dafault "/api/"
     */
    public function parseRequestUrl($pathUrl, $apiBase)
    {
        // find the starting position in the path based on the apiBase parameter
        $startPos = 0;
        if (strlen($apiBase) > 0) {
            $startPos = strpos($pathUrl, '/'.$apiBase.'/');
            if (false === $startPos) {
                throw new RestException('Invalid api url. Please check the server config.', 31010);
            }
            $startPos = $startPos + strlen($apiBase) + 2;
        }
        // find the url-parts before the api-request (everything before and including the apiBase)
        $tmpUrl = trim(substr($pathUrl, 1, $startPos - 2));
        $urlParts = explode('/', $tmpUrl);
        $tmpCount = count($urlParts);
        for ($i = 0; $i < $tmpCount; ++$i) {
            if ('' == $urlParts[$i]) {
                unset($urlParts[$i]);
            }
        }
        $urlParts = array_values($urlParts);

        // check if there is a prefix before the api-call
        $tmpCount = count($urlParts);
        if ($tmpCount > 1) {
            $prefix = $urlParts[$tmpCount - 2];
        } else {
            $prefix = '';
        }
        $this->prefix = trim($prefix);

        // get the api-call url-parts (everything after apiBase)
        $apiUrl = trim(substr($pathUrl, $startPos, strlen($pathUrl) - $startPos));
        $urlParts = explode('/', $apiUrl);
        $tmpCount = count($urlParts);
        for ($i = 0; $i < $tmpCount; ++$i) {
            if ('' == $urlParts[$i]) {
                unset($urlParts[$i]);
            }
        }
        $urlParts = array_values($urlParts);

        //the first two url-parts are required and MUST be version and module-name
        if (count($urlParts) < 2) {
            throw new RestException('Missing arguments: api-version and modulename are required.', 31020);
        }
        if (isset($urlParts[1])) {
            $this->moduleName = trim($urlParts[1]);
            unset($urlParts[1]);
        }
        if (isset($urlParts[0])) {
            $this->apiVersion = trim(strtolower($urlParts[0]));
            unset($urlParts[0]);
        }

        $this->restUrl = '/'.$this->moduleName;
        if (count($urlParts) > 0) {
            $this->restUrl .= '/'.implode('/', $urlParts);
        }
    }

    /**
     * Creates a RestRequest with the data from php globals.
     *
     * @return RestRequest The created request object
     */
    public static function processRequest()
    {
        $postData = urldecode(file_get_contents('php://input'));
        $postData = str_replace('%2B', '+', $postData);

        return new RestRequest($_SERVER, $_GET, $postData, $_COOKIE, $_FILES);
    }
}
