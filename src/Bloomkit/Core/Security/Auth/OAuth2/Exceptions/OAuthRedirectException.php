<?php

namespace Bloomkit\Core\Security\Auth\OAuth2\Exceptions;

/**
 * Definition of the OAuthRedirectException
 * This is used for forwarding an error to the redirect endpoint of a client in some cases.
 */
class OAuthRedirectException extends OAuthServerException
{
    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $state;

    /**
     * Constructor.
     *
     * @param string $redirectUri      The URI this error should forwarded to
     * @param int    $httpStatusCode   The HTTP error-code for this exception (404, 500, etc)
     * @param string $error            The OAuth error-message as defined in the rfc
     * @param string $errorDescription An optional error-description as defined in the rfc
     * @param string $errorUri         An optional help-url for this error as defined in the rfc
     * @param string $internalMessage  An additional information for the internal logs
     * @param string $state            The state parameter must be forwared back to the redirect URI too
     */
    public function __construct($redirectUri, $httpStatusCode, $error, $state = null, $errorDescription = null, $errorUri = null, $internalMessage = null)
    {
        parent::__construct($httpStatusCode, $error, $errorDescription, $errorUri, $internalMessage);
        $this->state = $state;
        $this->redirectUri = $redirectUri;
    }

    /**
     * Returns the redirect URI.
     *
     * @return string The redirect URI the error should forwared to
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Returns the state.
     *
     * @return string The state-parameter from the clients request
     */
    public function getState()
    {
        return $this->state;
    }
}
