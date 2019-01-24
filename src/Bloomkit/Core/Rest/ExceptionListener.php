<?php

namespace Bloomkit\Core\Rest;

use Psr\Log\LoggerInterface;
use Bloomkit\Core\Security\Exceptions\CredentialsMissingException;
use Bloomkit\Core\Security\Exceptions\AccessDeniedException;
use Bloomkit\Core\Security\Exceptions\AuthFailedException;
use Bloomkit\Core\Http\Exceptions\HttpNotFoundException;
use Bloomkit\Core\Security\Exceptions\BadCredentialsException;
use Bloomkit\Core\Http\HttpExceptionEvent;
use Bloomkit\Core\Rest\Exceptions\RestFaultException;
use Bloomkit\Core\Exceptions\NotFoundException;

/**
 * ExceptionListener for REST applications.
 *
 * Generates error-responses and logs
 */
class ExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger Logger to log messages to
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * EventHandler for HttpExceptions.
     *
     * @param HttpExceptionEvent $event The exception-event to handle
     */
    public function onException(HttpExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        if ($exception instanceof BadCredentialsException) {
            if (isset($this->logger)) {
                $this->logger->warning('BadCredentials Error:'.$exception->getMessage());
            }
            $response = RestResponse::createFault(403, 'No credentials found');
        } elseif ($exception instanceof CredentialsMissingException) {
            if (isset($this->logger)) {
                $this->logger->warning('BadCredentials Error:'.$exception->getMessage());
            }
            $response = RestResponse::createFault(401, 'No credentials found');
        } elseif ($exception instanceof HttpNotFoundException) {
            if (isset($this->logger)) {
                $this->logger->warning('Not found:'.$exception->getMessage());
            }
            $response = RestResponse::createFault(404, $exception->getMessage());
        } elseif ($exception instanceof NotFoundException) {
            if (isset($this->logger)) {
                $this->logger->warning('Not found:'.$exception->getMessage());
            }
            $response = RestResponse::createFault(404, $exception->getMessage());
        } elseif ($exception instanceof AuthFailedException) {
            if (isset($this->logger)) {
                $this->logger->warning('Authentication failed:'.$exception->getMessage());
            }
            $response = RestResponse::createFault(401, $exception->getMessage(), $exception->getCode());
        } elseif ($exception instanceof AccessDeniedException) {
            if (isset($this->logger)) {
                $this->logger->warning('Access denied:'.$exception->getMessage());
            }
            $response = RestResponse::createFault(403, $exception->getMessage());
        } elseif ($exception instanceof RestFaultException) {
            if (isset($this->logger)) {
                $this->logger->warning('REST fault:'.$exception->getMessage());
            }
            $response = RestResponse::createFault($exception->getStatusCode(), $exception->getMessage(), $exception->getFaultCode());
        } elseif ($exception instanceof \Exception) {
            if (isset($this->logger)) {
                $this->logger->error('Unknown error:'.$exception->getMessage());
            }
            $response = RestResponse::createFault(500, 'An unknown error has occured, please contact the administrator.');
        }

        if (isset($response)) {
            $event->setResponse($response);
        }
    }
}
