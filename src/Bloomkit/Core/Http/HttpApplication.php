<?php
namespace Bloomkit\Core\Http;

use Bloomkit\Core\Application\Application;

class HttpApplication extends Application
{

    /**
     * Constuctor
     *
     * @inheritdoc
     */
    public function __construct($appName = 'UNKNOWN', $appVersion = '0.0', $basePath = null, $config = array())
    {
        parent::__construct($appName, $appVersion, $basePath, $config);        
        
        $this->registerFactory('routes', 'Bloomkit\Core\Routing\RouteCollection', TRUE);
        $this->registerFactory('url_matcher', 'Bloomkit\Core\Routing\UrlMatcher', TRUE);
        
        $this->setAlias('Bloomkit\Core\Routing\RouteCollection', 'routes');
    }
    
    /**
     * Returns the url matcher
     *
     * @return \Bloomkit\Core\Routing\UrlMatcher
     */
    public function getUrlMatcher()
    {
        return $this['url_matcher'];
    }
    
    /**
     * Start the application
     */
    public function run()
    {
        $request = HttpRequest::processRequest();
        $response = $this->handle($request);
        return $response;
    }

    private function handle(HttpRequest $request)
    {
        try {
            $event = new HttpEvent($request);
            $this->getEventManager()->triggerEvent(HttpEvents::REQUEST, $event);
            
            if ($event->hasResponse()) {
                $this['eventManager']->triggerEvent(HttpEvents::RESPONSE, $event);
                return $event->getResponse();
            }
            
            $matcher = $this->getUrlMatcher();
            $parameters = $matcher->match($request->getPathUrl(), $request->getHttpMethod());
            
        } catch (\Exception $e) {
            return $this->handleException($e, $request);
        }
    }

    /**
     * Handles an exception by trying to convert it to a Response.
     *
     * @param \Exception $e
     * @param HttpRequest $request
     * 
     * @return HttpResponse
     *
     * @throws \Exception
     */
    private function handleException(\Exception $e, HttpRequest $request)
    {
        $event = new HttpExceptionEvent($request, $e);
        $this['eventManager']->triggerEvent(HttpEvents::EXCEPTION, $event);
    
        // a listener might have replaced the exception
        $e = $event->getException();
    
        if (! $event->hasResponse())
            throw $e;

        return $event->getResponse();
    }
}