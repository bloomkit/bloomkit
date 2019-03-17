<?php

namespace Bloomkit\Core\Security;

use Bloomkit\Core\Security\Exceptions\AuthException;
use Bloomkit\Core\Security\Exceptions\CredentialsMissingException;
use Bloomkit\Core\Http\HttpEvent;
use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Security\EntryPoint\AuthEntryPointInterface;
use Bloomkit\Core\Http\HttpExceptionEvent;
use Psr\Log\LoggerInterface;

class Firewall
{
    private $authEntryPoint;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $listeners = [];

    private $context;

    private $contextKey;

    /**
     * Constructor.
     */
    public function __construct(SecurityContext $context, LoggerInterface $logger = null)
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

    public function handleAuthenticationException(HttpExceptionEvent $event, AuthException $exception)
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

        $session = $request->getSession();
        $token = $session->get('_security_'.$this->contextKey);

        $token = unserialize($token);
        if ($token == false) {
            $token = null;
        }

        if (null !== $this->logger) {
            $this->logger->debug('Read SecurityContext from the session');
        }

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
        if (!is_null($token)) {
            if ($token->isStateful()) {
                $session->set('_security_'.$this->contextKey, serialize($token));
            }
        }
    }

    public function handleRequestFinishEvent(HttpEvent $event)
    {
    }

    public function setAuthEntryPoint(AuthEntryPointInterface $authEntryPoint)
    {
        $this->authEntryPoint = $authEntryPoint;
    }

    private function startAuthentication(HttpRequest $request, AuthException $authException)
    {
        if (null === $this->authEntryPoint) {
            throw $authException;
        }

        if (null !== $this->logger) {
            $this->logger->debug('Calling Authentication entry point');
        }

        return $this->authEntryPoint->start($request, $authException);
    }
}
