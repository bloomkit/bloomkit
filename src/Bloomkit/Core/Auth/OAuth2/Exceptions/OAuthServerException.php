<?php

namespace Bloomkit\Core\Auth\OAuth2\Exceptions;

/**
 * Definition of the OAuthServerException.
 */
class OAuthServerException extends \Exception
{
    /**
     * @var int
     */
    protected $httpCode;

    /**
     * @var string
     */
    protected $errorDesc;

    /**
     * @var string
     */
    protected $errorUri;

    /**
     * @var string
     */
    protected $internalMessage;

    /**
     * Constructor.
     *
     * @param int    $httpStatusCode   The HTTP error-code for this exception (404, 500, etc)
     * @param string $error            The OAuth error-message as defined in the rfc
     * @param string $errorDescription An optional error-description as defined in the rfc
     * @param string $errorUri         An optional help-url for this error as defined in the rfc
     * @param string $internalMessage  An additional information for the internal logs
     */
    public function __construct($httpStatusCode, $error, $errorDescription = null, $errorUri = null, $internalMessage = null)
    {
        parent::__construct($error);

        $this->httpCode = $httpStatusCode;
        $this->errorDesc = $errorDescription;
        $this->errorUri = $errorUri;
        $this->internalMessage = $internalMessage;
    }

    /**
     * Returns the error description.
     *
     * @return string The error description
     */
    public function getDescription()
    {
        return $this->errorDesc;
    }

    /**
     * Returns the internal error-message (for internal logs etc).
     *
     * @return string The internal message
     */
    public function getInternalMessage()
    {
        return $this->internalMessage;
    }

    /**
     * Returns the error URI.
     *
     * @return string The error URI
     */
    public function getUri()
    {
        return $this->errorUri;
    }

    /**
     * Returns the Http status-code.
     *
     * @return int The Http status code
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }
}
