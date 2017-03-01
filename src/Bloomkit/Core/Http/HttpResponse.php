<?php
namespace Bloomkit\Core\Http;

use Bloomkit\Core\Utilities\Repository;

class HttpResponse
{
	/**
	 * @var string
	 */	
    protected $content;

    /**
     * @var Repository
     */    
    protected $headers;

    /**
     * @var Repository
     */    
    protected $cookies;

    /**
     * @var int
     */    
    protected $statusCode;

    /**
     * @var string
     */    
    protected $charset;

    /**
     * @var string
     */    
    protected $httpVersion;

    /**
     * Constructor
     *
     * @param string	$content
     * @param int		$statusCode
     * @param array		$headers
     * @param array		$cookies	
     */
    public function __construct($content = '', $statusCode = 200, $headers = array(), $cookies = array())
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->httpVersion = '1.0';
        $this->headers = new Repository($headers);        
        $this->cookies = new Repository($cookies);
    }

    /**
     * Create and return a httpResponse with the given statusCode and message
     *
     * @return HttpResponse
     */
    public static function createResponse($statusCode, $message)
    {
    	$headers = array(
    			'Content-type' => 'text/html; charset=utf-8'
    	);
    	return new HttpResponse($message, $statusCode, $headers);
    }

    /**
     * Return the content
     *
     * @return string
     */
    public function getContent()
    {
    	return $this->content;
    }
    
    /**
     * Return the status code
     *
     * @return int
     */
    public function getStatusCode()
    {
    	return $this->statusCode;
    }
    
    /**
     * Returns a label for a http status code
     *
     * @param int	$status
     *
     * @return string
     */    
    public static function getStatusCodeMessage($status)
    {
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
        	102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
        	207 => 'Multi-Status',
			208 => 'Already Reported',
        	226 => 'IM Used',
        	300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
        	308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
        	418 => 'Im a teapot',
        	421 => 'Misdirected Request',
        	422 => 'Unprocessable Entity',
        	423 => 'Locked',
        	424 => 'Failed Dependency',
        	426 => 'Upgrade Required',
        	428 => 'Precondition Required',
        	429 => 'Too Many Requests',
        	431 => 'Request Header Fields Too Large',
        	451 => 'Unavailable For Legal Reasons',        		        		        		
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507	=> 'Insufficient Storage',
			508	=> 'Loop Detected',
			510 => 'Not Extended',
			511 => 'Network Authentication',        		        		
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    /**
     * Return true if the response has a client error statuscode
     *
     * @return bool
     */
    public function isClientError()
    {
    	return $this->statusCode >= 400 && $this->statusCode < 500;
    }
    
    /**
     * Return true if the response has a redirect statuscode
     *
     * @return bool
     */
    public function isRedirect()
    {
    	return $this->statusCode >= 300 && $this->statusCode < 400;
    }
    
    /**
     * Return true if the response has a server error statuscode
     *
     * @return bool
     */
    public function isServerError()
    {
    	return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Send http headers and content
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }
    
    /**
     * Send the content
     */        
    public function sendContent()
    {
    	echo $this->content;
    }    
    
    /**
     * Send the http headers and the cookies (if set)
     */    
    public function sendHeaders()
    {
    	if (headers_sent())
    		return;
    
    	header(sprintf('HTTP/%s %s %s', $this->httpVersion, $this->statusCode, $this->getStatusCodeMessage($this->statusCode)));
    
    	$headers = $this->headers->getItems();
    	foreach ($headers as $key => $value)
    		header($key . ': ' . $value, false);
    
    	foreach ($this->cookies as $cookie) {
    		if (($cookie instanceof Cookie)==FALSE)
    			continue;
    		if ($cookie->isRaw()) {
    			setrawcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
    		} else {
    			setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
    		}
    	}
    }    

    /**
     * Set the content
     *
     * @param string	$content	The response content
     */    
    public function setContent($content)
    {
    	$this->content = $content;
    }    
    
    /**
     * Set the http status code
     *
     * @param int	$code 	The http status code
     */
    public function setStatusCode($code)
    {
    	$this->statusCode = $statusCode;
    }    
} 