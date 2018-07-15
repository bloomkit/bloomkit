<?php

namespace Bloomkit\Core\Http\Session;

use Bloomkit\Core\Http\HttpApplication;
use Bloomkit\Core\Http\HttpEvents;
use Bloomkit\Core\Http\HttpEvent;
use Bloomkit\Core\EventManager\EventSubscriberInterface;

/**
 * The SessionListener reacts on incoming HttpEvents and add the
 * session (if any) to the request.
 */
class SessionListener implements EventSubscriberInterface
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
     * Return the applications session object.
     *
     * @return Session The applications session object
     */
    protected function getSession()
    {
        return $this->app->session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            HttpEvents::REQUEST => ['onKernelRequest', 128],
        ];
    }

    /**
     * Callback for HttpEvents::REQUEST - add the session (if any) to the request.
     */
    public function onKernelRequest(HttpEvent $event)
    {
        $request = $event->getRequest();

        if ($request->hasSession()) {
            return;
        }

        $session = $this->getSession();
        if (is_null($session)) {
            return;
        }

        $request->setSession($session);
    }
}
