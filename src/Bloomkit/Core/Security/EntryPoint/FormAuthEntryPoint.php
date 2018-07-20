<?php
namespace Bloomkit\Core\Security\EntryPoint;

use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Http\HttpRedirectResponse;
use Bloomkit\Core\Security\Exceptions\AuthException;
use Bloomkit\Core\Security\Exceptions\BadCredentialsException;

class FormAuthEntryPoint implements AuthEntryPointInterface
{
    private $loginPath;

    /**
     * Constructor.
     *
     * @param string $loginPath Path to the login form
     */
    public function __construct($loginPath = '/login')
    {
        $this->loginPath = $loginPath;
    }

    public function start(HttpRequest $request, AuthException $e = null)
    {
        $session = $request->getSession();

        if (isset($e)) {
            if ($e instanceof BadCredentialsException) {
                $session->getFlashBag()->add('error', 'Login failed. Please check username and password');
            } else if ($e instanceof \Bloomkit\Core\Security\Exceptions\CredentialsMissingException) {
                //
            } else if ($e instanceof \Exception) {
                $session->getFlashBag()->add('error', 'Login currently not available. Please try again later');
            }
        }

        $continue = $request->getGetParams()->get('continue', null);

        if (is_null($continue)) {
            $paramStr = $request->getParamStr();
            if ($paramStr !== '')
                $paramStr = '?' . $paramStr;
                $continue = $request->getFullUrl() . $paramStr;
        }

        $path = $this->loginPath . '?continue=' . urlencode($continue);
        if ($path[0] == '/')
            $path = $request->getBaseUrl() . $path;

            return new HttpRedirectResponse($path, 302);
    }
}