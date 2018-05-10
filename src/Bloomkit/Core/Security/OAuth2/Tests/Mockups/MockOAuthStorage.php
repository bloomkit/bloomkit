<?php

namespace Bloomkit\Core\Security\OAuth2\Tests\Mockups;

use Bloomkit\Core\Security\User\UserInterface;
use Bloomkit\Core\Security\User\User;
use Bloomkit\Core\Security\OAuth2\OAuthClient;
use Bloomkit\Core\Security\OAuth2\Storage\OAuthStorageInterface;
use Bloomkit\Core\Security\OAuth2\OAuthToken;

class MockOAuthStorage implements OAuthStorageInterface
{
    public $authCode;

    public $accessToken;

    public $client;

    public function __construct()
    {
    }

    /**
     * @return OAuthClient
     */
    public function getClient($clientId)
    {
        $client = null;
        if ('valid' == $clientId) {
            $client = new OAuthClient($clientId);
            $client->setTokenLifetime(3600);
        } elseif ('exception' == $clientId) {
            throw new \Exception('forced error');
        } elseif ('valid-with-redirect' == $clientId) {
            $client = new OAuthClient($clientId);
            $client->setTokenLifetime(3600);
            $client->setRedirectUris([
                'https://www.example.com',
            ]);
        } elseif ('valid-with-user' == $clientId) {
            $client = new OAuthClient($clientId);
            $client->setTokenLifetime(3600);
            $client->setUserId('alloweduser');
            $client->setRedirectUris([
                'https://www.example.com',
            ]);
        } elseif ('valid-with-secret' == $clientId) {
            $client = new OAuthClient($clientId);
            $client->setTokenLifetime(3600);
            $client->setSecret('secret');
            $client->setRedirectUris([
                'https://www.example.com',
            ]);
        }
        $this->client = $client;

        return $client;
    }

    public function createAuthCode(OAuthClient $client, UserInterface $user, $authCode, $redirectUri, $scope, $expire)
    {
        $tmpAuthCode = [];
        $tmpAuthCode['client'] = $client;
        $tmpAuthCode['user'] = $user;
        $tmpAuthCode['authCode'] = $authCode;
        $tmpAuthCode['redirectUri'] = $redirectUri;
        $tmpAuthCode['scope'] = $scope;
        $tmpAuthCode['expire'] = $expire;
        $this->authCode = $tmpAuthCode;
    }

    public function getUser($userId)
    {
        if ('valid' == $userId) {
            return new User('valid-user');
        }
    }

    public function getAuthCode($code)
    {
    }

    public function getAccessToken($code)
    {
    }

    public function getRefreshToken($code)
    {
        $token = null;
        if ('valid' == $code) {
            $token = new OAuthToken('valid', 'valid', $code, time() + 3600);
            $token->setScope('myscope');
        } elseif ('valid-with-secret' == $code) {
            $token = new OAuthToken('valid-with-secret', 'valid', $code, time() + 3600);
            $token->setScope('myscope');
        } elseif ('exception' == $code) {
            throw new \Exception('forced error');
        } elseif ('expired' == $code) {
            $token = new OAuthToken('valid', 'valid', $code, 1);
            $token->setScope('myscope');
        }

        return $token;
    }

    public function createAccessToken(OAuthClient $client, UserInterface $user, $accessToken, $scope, $expire)
    {
        $tmpAccessToken = [];
        $tmpAccessToken['client'] = $client;
        $tmpAccessToken['user'] = $user;
        $tmpAccessToken['accessToken'] = $accessToken;
        $tmpAccessToken['scope'] = $scope;
        $tmpAccessToken['expire'] = $expire;
        $this->accessToken = $tmpAccessToken;

        return true;
    }

    public function createRefreshToken(OAuthClient $client, UserInterface $user, $accessToken, $refreshToken, $scope, $expire)
    {
        return true;
    }

    public function invalidateAuthCode($code)
    {
        return true;
    }
}
