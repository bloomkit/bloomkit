<?php

namespace Bloomkit\Core\Security\OAuth2;

/**
 * Representation of a OAuthClient.
 */
class OAuthClient implements OAuthClientInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var array
     */
    private $redirectUris = [];

    /**
     * @var int
     */
    private $secret;

    /**
     * @var int
     */
    private $tokenLifetime = -1;

    /**
     * @var string
     */
    private $userId;

    /**
     * Constructor.
     *
     * @param string $id The client id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    /**
     * Returns the client secret (if any).
     *
     * @return string|null The client secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Returns the lifetime for tokens issued to this client.
     *
     * @return int The configured token lifetime
     */
    public function getTokenLifetime()
    {
        return $this->tokenLifetime;
    }

    /**
     * Returns the id of the user this client is associated with (if any).
     *
     * @return string|null The users id associated with this client
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the redirect URIs, that are allowed for this client.
     *
     * @param array The redirect URIs that are allowed
     */
    public function setRedirectUris(array $redirectUris)
    {
        $this->redirectUris = $redirectUris;
    }

    /**
     * Set the client secret.
     *
     * @param string The client secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Set the lifetime for tokens for this client.
     *
     * @param int The lifetime for issued tokens
     */
    public function setTokenLifetime($value)
    {
        if ((false == is_numeric($value)) || ($value < 0)) {
            $this->tokenLifetime = -1;
        } else {
            $this->tokenLifetime = $value;
        }
    }

    /**
     * Set the user id this client is associated with.
     *
     * @param string The user id
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}
