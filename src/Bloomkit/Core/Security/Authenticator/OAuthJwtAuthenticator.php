<?php

namespace Bloomkit\Core\Security\Authenticator;

use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Security\Token\OAuthBearerToken;
use Bloomkit\Core\Security\Token\Token;
use Bloomkit\Core\Security\Exceptions\AuthFailedException;
use Bloomkit\Core\Security\Exceptions\CredentialsMissingException;
use Bloomkit\Core\Security\User\UserProviderInterface;
use Bloomkit\Core\Security\Exceptions\BadCredentialsException;
use Bloomkit\Core\Security\OAuth2\OAuthUtils;
use Bloomkit\Core\Security\OpenId\JsonWebToken;
use Bloomkit\Core\Exceptions\ConfigurationException;

/**
 * Authenticator for OAuthJwtTokens.
 */
class OAuthJwtAuthenticator implements AuthenticatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function authenticateToken(Token $token, UserProviderInterface $userProvider, $options = [])
    {
        if (is_null($token)) {
            throw new AuthFailedException('No token provided');
        }

        $tokenCode = $token->getBearerToken();
        try {
            $jwt = JsonWebToken::decode($tokenCode);
        } catch (\Exception $e) {
            if ((isset($options['oauthFallback'])) && ($options['oauthFallback'] === true)) {
                $oAuthAuthenticator = new OAuthTokenAuthenticator();

                return $oAuthAuthenticator->authenticateToken($token, $userProvider, $options);
            } else {
                throw new AuthFailedException('Not a valid JWT: '.$e->getMessage());
            }
        }

        if (isset($options['jwtAlgorithm'])) {
            $jwtAlgorithm = $options['jwtAlgorithm'];
        }

        if (!isset($jwtAlgorithm)) {
            throw new ConfigurationException('Authentication failure: OAuthJwtAuthenticator is not '.
                       'properly configured: jwtAlgorithm is not set');
        }

        if (empty(JsonWebToken::$supportedAlgorithms[$jwtAlgorithm])) {
            throw new ConfigurationException('Authentication failure: OAuthJwtAuthenticator is not '.
                       'properly configured: '.$jwtAlgorithm.' is not a valid algorithm');
        }

        if (isset($options['jwtKey'])) {
            $jwtKey = $options['jwtKey'];
        }

        if (is_null($jwtKey)) {
            throw new ConfigurationException('Authentication failure: OAuthJwtAuthenticator is not '.
                'properly configured: jwtKey is not set');
        }

        try {
            $result = $jwt->verify($jwtKey, $jwtAlgorithm);
        } catch (Exception $e) {
            throw new AuthFailedException('JWT verification failed: '.$e->getMessage());
        }

        if (!$result) {
            throw new AuthFailedException('JWT verification failed');
        }
        $user = $userProvider->loadUserById($jwt->getSub());
        if (is_null($user)) {
            throw new BadCredentialsException('Invalid token owner');
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
            $tokenCode = $request->getGetParams()->get('access_code', null);
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
