<?php

namespace Bloomkit\Core\Security\OAuth2;

/**
 * Representation of an OAuth AuthCode.
 */
class OAuthAuthCode extends OAuthToken
{
    /**
     * @var string
     */
    protected $redirectUris;

    /**
     * Constructor.
     *
     * @param string $clientId     The id of the corresponding client
     * @param string $userId       The id of the user this token is for
     * @param string $code         The AuthCode
     * @param int    $expiresAt    Timestamp when the token expired
     * @param string $scope        The scope of the token
     * @param array  $redirectUris The redirect URIs to be stored with this AuthCode
     */
    public function __construct($clientId, $userId, $code, $expiresAt, $scope, $redirectUris)
    {
        parent::__construct($clientId, $userId, $code, $expiresAt, $scope);
        $this->redirectUris = $redirectUris;
    }

    /**
     * Returns the redirect URIs.
     *
     * @return array The redirect URIs associated with this AuthCode
     */
    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    /**
     * Set the redirect URIs.
     *
     * @param array The redirect URIs to associate with this AuthCode
     */
    public function setRedirectUris($redirectUris)
    {
        $this->redirectUris = $redirectUris;
    }
}
