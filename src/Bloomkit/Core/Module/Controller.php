<?php

namespace Bloomkit\Core\Module;

use Bloomkit\Core\Http\HttpRequest;

/**
 * Representation of a bloomkit module controller.
 */
class Controller
{
    /**
     * @var HttpRequest
     */
    protected $request;

    public function __construct()
    {
    }

    /**
     * @deprecated define permission on route registration
     */
    public function checkPermission($action, $resource)
    {
    }

    /**
     * Returns the HttpRequest.
     *
     * @return HttpRequest The Http request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the HttpRequest.
     *
     * @param HttpRequest $request The request to set
     */
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
    }
}
