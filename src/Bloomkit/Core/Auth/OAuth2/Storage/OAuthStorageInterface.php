<?php

namespace Bloomkit\Core\Auth\OAuth2\Storage;

use Bloomkit\Core\Auth\OAuth2\OAuthClient;
use Bloomkit\Core\Security\User\UserInterface;
use Bloomkit\Core\Auth\OAuth2\OAuthToken;
use Bloomkit\Core\Auth\OAuth2\OAuthAuthCode;

/**
 * Defines how an OAuthStorage Handler should look like.
 */
interface OAuthStorageInterface
{
    /**
     * Returns an access-token by its code.
     *
     * @param string $code The access-token code
     *
     * @return OAuthToken|null
     */
    public function getAccessToken($code);

    /**
     * Returns an auth-code by its code.
     *
     * @param string $code The auth-code
     *
     * @return OAuthAuthCode|null
     */
    public function getAuthCode($code);

    /**
     * Returns an oauth client by its id.
     *
     * @param string $clientId The id of the client
     *
     * @return OAuthClient|null
     */
    public function getClient($clientId);

    /**
     * Returns a refresh-token by its code.
     *
     * @param string $code The refresh-token code
     *
     * @return OAuthToken|null
     */
    public function getRefreshToken($code);

    /**
     * Returns a user by its id.
     *
     * @param string $userId The id of the user
     *
     * @return UserInterface|null
     */
    public function getUser($userId);

    /**
     * Stores an auth-code.
     *
     * @param OAuthClient   $client      The client this auth-code is issued to
     * @param UserInterface $user        The user this auth-code is legitimated by
     * @param string        $authCode    The issued code
     * @param string        $redirectUri The redirect URI provided by the request
     * @param string        $scope       The scope requested by the client
     * @param int           $expire      The expiration timestamp of the token
     *
     * @return bool Returns true if successfull
     */
    public function createAuthCode(OAuthClient $client, UserInterface $user, $authCode, $redirectUri, $scope, $expire);

    /**
     * Stores an access-token.
     *
     * @param OAuthClient   $client      The client this auth-code is issued to
     * @param UserInterface $user        The user this auth-code is legitimated by
     * @param string        $accessToken The issued token
     * @param string        $scope       The scope requested by the client
     * @param int           $expire      The expiration timestamp of the token
     *
     * @return bool Returns true if successfull
     */
    public function createAccessToken(OAuthClient $client, UserInterface $user, $accessToken, $scope, $expire);

    /**
     * Stores a refresh-token.
     *
     * @param OAuthClient   $client       The client this auth-code is issued to
     * @param UserInterface $user         The user this auth-code is legitimated by
     * @param string        $accessToken  The access-token the refresh-token is used for
     * @param string        $refreshToken The refresh-token
     * @param string        $scope        The scope requested by the client
     * @param int           $expire       The expiration timestamp of the refresh-token
     *
     * @return bool Returns true if successfull
     */
    public function createRefreshToken(OAuthClient $client, UserInterface $user, $accessToken, $refreshToken, $scope, $expire);

    /**
     * Invalidates an issued auth-code.
     *
     * @param string $code The auth-code
     *
     * @return bool Returns true if successfull
     */
    public function invalidateAuthCode($code);
}
