<?php

namespace Probelix\PbxAuth\Security\Authentication;

use Bloomkit\Core\Security\Token\OAuthBearerToken;
use Bloomkit\Core\Security\Token\Token;
use Bloomkit\Core\Security\AuthenticatorInterface;
use Bloomkit\Core\Security\Exceptions\AuthFailedException;
use Bloomkit\Core\Security\Exceptions\CredentialsMissingException;
use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Security\User\UserProviderInterface;
use Bloomkit\Core\Security\Exceptions\BadCredentialsException;
use Bloomkit\Core\Security\OAuth2\OAuthUtils;

/**
 * Auhenticator for OAuthTokens.
 */
class OAuthTokenAuthenticator implements AuthenticatorInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('LoginFormAuthenticator');
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateToken(Token $token, UserProviderInterface $userProvider)
    {
        if (is_null($token)) {
            throw new AuthFailedException('No token provided');
        }
        $tokenCode = $token->getBearerToken();
        $accessToken = $userProvider->loadOauthAccessToken($tokenCode);
        if (is_null($accessToken)) {
            throw new AuthFailedException('Invalid token', 12346);
        }
        if ($accessToken->hasExpired()) {
            throw new AuthFailedException('Token expired', 12345);
        }
        $user = $userProvider->loadUserById($accessToken->getUserId());
        if (is_null($user)) {
            throw new BadCredentialsException('Invalid token owner', 12347);
        }
        $token->setUser($user);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(HttpRequest $request)
    {
        $tokenCode = OAuthUtils::getBearerTokenFromRequest($request);
        if (is_null($tokenCode)) {
            $tokenCode = $request->getGetParams()->getValue('access_code', null);
        }
        if (is_null($tokenCode)) {
            throw new CredentialsMissingException('No token found in request');
        }
        $token = new OAuthBearerToken($tokenCode);
        $token->setStateful(false);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(Token $token)
    {
        return $token instanceof OAuthBearerToken;
    }
}
