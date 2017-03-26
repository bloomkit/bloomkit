<?php
namespace Bloomkit\Core\Http;

use Bloomkit\Core\EventManager\Event;
use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Http\HttpResponse;

class HttpEvent extends Event
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * Constructor
     *
     * @param HttpRequest   $request
     */
    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Return the http-request
     *
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return the http-response (if set)
     *
     * @return HttpResponse|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the http-response and stop event-processing
     *
     * @return HttpResponse
     */
    public function setResponse(HttpResponse $response)
    {
        $this->response = $response;
        $this->stopProcessing();
    }

    /**
     * Check if a response is set
     *
     * @return boolean
     */
    public function hasResponse()
    {
        return isset($this->response);
    }
}