<?php

namespace Bloomkit\Core\Rest\Exceptions;

/**
 * Exception representing a REST fault.
 */
class RestFaultException extends RestException
{
    /** @var int */
    private $statusCode;

    /** @var int */
    private $faultCode;

    /**
     * @param int    $statusCode The HTTP status-code to return
     * @param string $message    The REST error message
     * @param int    $faultCode  The REST error code
     */
    public function __construct($statusCode, $message, $faultCode = 0)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->faultCode = $faultCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return int
     */
    public function getFaultCode()
    {
        return $this->faultCode;
    }
}
