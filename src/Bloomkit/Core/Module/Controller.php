<?php

namespace Bloomkit\Core;

use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Application\Application;
use Bloomkit\Core\Security\Exceptions\AccessDeniedException;

/**
 * Representation of a bloomkit module controller.
 */
class Controller
{
    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var Application
     */
    protected $application;

    /**
     * Constructor.
     *
     * @param Application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Check the current users permission to perform an action on a resource.
     *
     * @param string $action   The action to check permission to
     * @param string $resource The resource to check permission to
     *
     * @return bool True if permission is granted
     *
     * @throws AccessDeniedException If permission is prohibited
     */
    public function checkPermission($action, $resource)
    {
        $token = $this->application->getSecurityContext()->getToken();

        if (is_null($token)) {
            throw new AccessDeniedException('No token found');
        }
        $user = $token->getUser();

        if (is_null($user)) {
            throw new AccessDeniedException('No user found');
        }
        $policy = $user->getPolicy();

        if (is_null($policy)) {
            throw new AccessDeniedException('No policy found');
        }
        if (true !== $policy->isAllowed($action, $resource)) {
            throw new AccessDeniedException('Permission denied');
        }

        return true;
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
