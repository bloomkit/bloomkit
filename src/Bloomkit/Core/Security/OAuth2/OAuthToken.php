<?php

namespace Bloomkit\Core\Security\OAuth2;

/**
 * Representation of a generic OAuth token (authCode, authToken & refreshToken).
 */
class OAuthToken
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var int
     */
    protected $expiresAt;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $userId;

    /**
     * Constructor.
     *
     * @param string $clientId  The id of the corresponding client
     * @param string $userId    The id of the user this token is for
     * @param string $token     The token code
     * @param int    $expiresAt Timestamp when the token expired
     * @param string $scope     The scope of the token
     */
    public function __construct($clientId, $userId, $token, $expiresAt = null, $scope = null)
    {
        $this->userId = $userId;
        $this->setClientId($clientId);
        $this->setToken($token);
        $this->setExpiresAt($expiresAt);
        $this->setScope($scope);
    }

    /**
     * Returns the client id.
     *
     * @return string The id of the corresponding client
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Returns the duration in seconds when the token expires.
     *
     * @return int The time in seconds when this token expires
     */
    public function getExpiresIn()
    {
        if ($this->expiresAt) {
            $expires = $this->expiresAt - time();
            if ($expires < 0) {
                $expires = 0;
            }

            return $expires;
        } else {
            return PHP_INT_MAX;
        }
    }

    /**
     * Returns the scope of the token.
     *
     * @return string The scope of the token
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Returns the token code.
     *
     * @return string The token code
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns the associated user-id.
     *
     * @return string The id of the user
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Check if token is expired.
     *
     * @return bool true if expired, false if not
     */
    public function hasExpired()
    {
        if ($this->expiresAt <= 0) {
            return false;
        }

        return time() > $this->expiresAt;
    }

    /**
     * Set the client id.
     *
     * @param string $id The id of the corresponding client
     */
    public function setClientId($id)
    {
        $this->clientId = $id;
    }

    /**
     * Set the expiration timestamp.
     *
     * @param int $timestamp The timestamp when this token expires
     */
    public function setExpiresAt($timestamp)
    {
        $this->expiresAt = $timestamp;
    }

    /**
     * Set the scope.
     *
     * @param string $scope The scope of the token
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Set the token.
     *
     * @param string $token The token code
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Set the user id.
     *
     * @param string $id The id of the user
     */
    public function setUserId($id)
    {
        $this->userId = $id;
    }
}
