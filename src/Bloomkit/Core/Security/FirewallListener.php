<?php

namespace Bloomkit\Core\Security;

use Bloomkit\Core\Security\Exceptions\CredentialsMissingException;
use Bloomkit\Core\Security\Exceptions\BadCredentialsException;
use Bloomkit\Core\EventManager\EventSubscriberInterface;
use Bloomkit\Core\Http\HttpEvents;
use Bloomkit\Core\Http\HttpEvent;
use Bloomkit\Core\Http\HttpExceptionEvent;
use Bloomkit\Core\Security\Exceptions\AuthFailedException;

/**
 * The FirewallListener reacts on HttpEvents and map them to the
 * firewalls handler.
 */
class FirewallListener implements EventSubscriberInterface
{
    /**
     * @var HttpApplication
     */
    private $app;

    /**
     * Constructor.
     *
     * @param HttpApplication $app The application object
     */
    public function __construct(HttpApplication $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            HttpEvents::REQUEST => ['onKernelRequest', 100],
            HttpEvents::RESPONSE => ['onKernelResponse', 100],
            HttpEvents::EXCEPTION => ['onException', 8],
            HttpEvents::FINISH_REQUEST => ['onKernelFinishRequest', -1024],
        ];
    }

    /**
     * Callback for HttpEvents::EXCEPTION - call the firewalls exception handler.
     */
    public function onException(HttpExceptionEvent $event)
    {
        $exception = $event->getException();
        do {
            if ($exception instanceof AuthFailedException) {
                return $this->firewall->handleAuthenticationException($event, $exception);
            } elseif ($exception instanceof CredentialsMissingException) {
                return $this->firewall->handleCredentialsMissingException($event, $exception);
            } elseif ($exception instanceof BadCredentialsException) {
                return $this->firewall->handleAuthenticationException($event, $exception);
            } elseif ($exception instanceof LogoutException) {
                return $this->firewall->handleLogoutException($event, $exception);
            }
            $exception = $exception->getPrevious();
        } while (!is_null($exception));
    }

    /**
     * Callback for HttpEvents::FINISH_REQUEST - call the firewalls handler.
     */
    public function onKernelFinishRequest(HttpEvent $event)
    {
        $this->firewall->handleRequestFinishEvent($event);
    }

    /**
     * Callback for HttpEvents::REQUEST - call the firewalls handler.
     */
    public function onKernelRequest(HttpEvent $event)
    {
        $this->firewall->handleRequestEvent($event);
    }

    /**
     * Callback for HttpEvents::RESPONSE - call the firewalls handler.
     */
    public function onKernelResponse(HttpEvent $event)
    {
        $this->firewall->handleResponseEvent($event);
    }
}
