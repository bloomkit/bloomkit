<?php

namespace Bloomkit\Core\Security;

use Bloomkit\Core\Security\Exceptions\AuthenticationException;
use Bloomkit\Core\Security\Exceptions\CredentialsMissingException;
use Bloomkit\Core\Http\HttpEvent;
use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Security\EntryPoint\AuthenticationEntryPointInterface;
use Bloomkit\Core\Http\HttpExceptionEvent;

class Firewall
{
    private $authEntryPoint;

    private $logger;

    private $listeners = [];

    private $context;

    private $contextKey;

    /**
     * Constructor.
     */
    public function __construct(SecurityContext $context, $logger = null)
    {
        $this->logger = $logger;
        $this->context = $context;
        $this->contextKey = 'test';
    }

    public function addListener($listener)
    {
        $this->listeners[] = $listener;
    }

    public function handleCredentialsMissingException(HttpExceptionEvent $event, CredentialsMissingException $exception)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Authentication required; redirecting to authentication entry point (%s)', $exception->getMessage()));
        }

        try {
            $event->setResponse($this->startAuthentication($event->getRequest(), $exception));
        } catch (\Exception $e) {
            $event->setException($e);
        }
    }

    public function handleAuthenticationException(HttpExceptionEvent $event, AuthenticationException $exception)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Authentication exception occurred; redirecting to authentication entry point (%s)', $exception->getMessage()));
        }

        try {
            $event->setResponse($this->startAuthentication($event->getRequest(), $exception));
        } catch (\Exception $e) {
            $event->setException($e);
        }
    }

    public function handleRequestEvent(HttpEvent $event)
    {
        $request = $event->getRequest();
        // $session = $request->hasPreviousSession() ? $request->getSession() : null;

        $session = $request->getSession();
        $token = $session->get('_security_'.$this->contextKey);

        // if (null === $session || null === $token = $session->get('_security_'.$this->contextKey)) {
        // $this->context->setToken(null);
        // return;
        // }

        $token = unserialize($token);
        if ($token == false) {
            $token = null;
        }

        if (null !== $this->logger) {
            $this->logger->debug('Read SecurityContext from the session');
        }

        // if ($token instanceof TokenInterface) {
        // $token = $this->refreshUser($token);
        // } elseif (null !== $token) {
        // if (null !== $this->logger) {
        // $this->logger->warning(sprintf('Session includes a "%s" where a security token is expected', is_object($token) ? get_class($token) : gettype($token)));
        // }
        // $token = null;
        // }

        $this->context->setToken($token);

        foreach ($this->listeners as $listener) {
            $listener->handle($event);
            if ($event->hasResponse()) {
                break;
            }
        }
    }

    public function handleResponseEvent(HttpEvent $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        if (is_null($session)) {
            return;
        }

        $token = $this->context->getToken();
        if ((is_null($token)) || ($token instanceof AnonymousToken)) {
            // if ((null === $token = $this->context->getToken()) || () {
            // if ($request->hasPreviousSession()) {
            // $session->remove('_security_'.$this->contextKey);
            // }
        } else {
            if ($token->isStateful()) {
                $session->set('_security_'.$this->contextKey, serialize($token));
            }
        }
    }

    public function handleRequestFinishEvent(HttpEvent $event)
    {
    }

    public function setAuthEntryPoint(AuthenticationEntryPointInterface $authEntryPoint)
    {
        $this->authEntryPoint = $authEntryPoint;
    }

    private function startAuthentication(HttpRequest $request, AuthenticationException $authException)
    {
        if (null === $this->authEntryPoint) {
            throw $authException;
        }

        if (null !== $this->logger) {
            $this->logger->debug('Calling Authentication entry point');
        }

        if ($authException instanceof AccountStatusException) {
            // remove the security token to prevent infinite redirect loops
            $this->context->setToken(null);
        }

        return $this->authEntryPoint->start($request, $authException);
    }
}
