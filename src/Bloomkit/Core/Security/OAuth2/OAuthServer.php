<?php

namespace Bloomkit\Core\Security\OAuth2;

use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Http\HttpResponse;
use Bloomkit\Core\Http\HttpRedirectResponse;
use Bloomkit\Core\Security\User\UserInterface;
use Bloomkit\Core\Security\OAuth2\Exceptions\OAuthServerException;
use Bloomkit\Core\Security\OAuth2\Storage\OAuthStorageInterface;
use Bloomkit\Core\Utilities\GuidUtils;
use Bloomkit\Core\Security\OpenId\JsonWebToken;

/**
 * OAuth2.0 rfc6749 server implementation
 * Does NOT support the RessourceOwnerPasswordGrant and the ClientCredentialGrant.
 *
 * @see http://tools.ietf.org/html/rfc6749
 */
class OAuthServer
{
    /**
     * @var OAuthStorageInterface
     */
    private $storage;

    /**
     * @var bool
     */
    private $forceSsl;

    /**
     * Constructor.
     *
     * @param OAuthStorageInterface $storage  StorageHandler to access tokens, clients etc
     * @param bool                  $forceSsl As the rfc suggests, requests to unsecure endpoints are not supported by default
     */
    public function __construct(OAuthStorageInterface $storage, $forceSsl = true)
    {
        $this->storage = $storage;
        $this->forceSsl = $forceSsl;
    }

    /**
     * Authorization Endpoint as defined in rfc6749 section 3.1
     * IMPORTANT: This function expect the user-parameter to be a representation of an authenticated
     * user.
     * The authentification itself has to be done by the application calling this function.
     *
     * @param HttpRequest   $request The incoming http-request
     * @param UserInterface $user    A representation of the authenticated user
     *
     * @return HttpRedirectResponse The return-response
     */
    public function authorize(HttpRequest $request, UserInterface $user)
    {
        // only ssl allowed here
        if (($this->forceSsl) && (!$request->isSecure())) {
            throw new OAuthServerException(400, 'invalid_request', 'You have to use ssl to access this endpoint');
        }
        // as defined in the rfc section 3.1, GET MUST be supported, POST MAY
        if ('GET' == $request->getHttpMethod()) {
            $params = $request->getGetParams();
        } elseif ('POST' == $request->getHttpMethod()) {
            $params = $request->getPostParams();
        }

        // get the required client_id
        $clientId = filter_var($params->get('client_id', ''), FILTER_SANITIZE_STRING);
        if (0 == strlen($clientId)) {
            throw new OAuthServerException(400, 'invalid_request', 'No client_id supplied');
        }
        // get the required response_type
        $responseType = filter_var($params->get('response_type', ''), FILTER_SANITIZE_STRING);
        if (0 == strlen($responseType)) {
            throw new OAuthServerException(400, 'invalid_request', 'No response_type supplied');
        }
        // the responseType may contain a space-delemited list of values (for extensions like openid)
        $responseType = explode(' ', $responseType);

        // the response type must be 'code' (Authorization Code Grant) or 'token' (Implicit Grant)
        if ((false === array_search('code', $responseType)) && (false === array_search('token', $responseType))) {
            throw new OAuthServerException(400, 'unsupported_response_type', 'Unsupported response_type supplied');
        }
        // get the optional parameters
        $redirectUri = filter_var($params->get('redirect_uri', ''), FILTER_SANITIZE_URL);
        $scope = filter_var($params->get('scope', ''), FILTER_SANITIZE_STRING);
        $state = filter_var($params->get('state', ''), FILTER_SANITIZE_STRING);

        // try to find the client by the client_id
        try {
            $client = $this->storage->getClient($clientId);
        } catch (\Exception $e) {
            throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
        }
        if (is_null($client)) {
            throw new OAuthServerException(400, 'unauthorized_client', 'Client not found: '.$clientId);
        }
        // check the redirect uri
        if (('' !== $redirectUri) && (false === array_search($redirectUri, $client->getRedirectUris()))) {
            throw new OAuthServerException(403, 'access_denied', 'The redirect URI in the request did not match a registered redirect URI.');
        }
        // check if the client is assigned to a specific user and if so, does the user match?
        if ((!is_null($client->getUserId()) && ($user->getUserId() != $client->getUserId()))) {
            throw new OAuthServerException(403, 'access_denied', 'You are not allowed to use this client.');
        }
        // generate token code
        $tokenCode = $this->createTokenCode();

        // Parse RedirectUrl
        $uri_parts = parse_url($redirectUri);
        $params = [];

        // the response type must be 'code' (Authorization Code Grant) or 'token' (Implicit Grant)
        if (false !== array_search('code', $responseType)) {
            // handle Authorization Code Grant
            try {
                $this->storage->createAuthCode($client, $user, $tokenCode, $redirectUri, $scope, time() + 60);
            } catch (\Exception $e) {
                throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
            }

            $params['code'] = $tokenCode;
            if ((isset($state)) && ('' != $state)) {
                $params['state'] = $state;
            }

            $uri_parts['query'] = http_build_query($params);
            $redirectUri = OAuthUtils::buildUrl($uri_parts);

            return new HttpRedirectResponse($redirectUri);
        } elseif (false !== array_search('token', $responseType)) {
            // handle implicitGrant
            $lifetime = $client->getTokenLifetime();
            if ((false == is_numeric($lifetime)) || (($lifetime < 1))) {
                $lifetime = 3600;
            }

            try {
                $this->storage->createAccessToken($client, $user, $tokenCode, $scope, time() + $lifetime);
            } catch (\Exception $e) {
                throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
            }

            $params['access_token'] = $tokenCode;
            $params['token_type'] = 'bearer';
            $params['expires_in'] = $lifetime;

            if (false !== array_search('id_token', $responseType)) {
                $idToken = new JsonWebToken($request->getFullUrl(), $user->getUserId(), $client->getClientId(), time() + $lifetime, time());
                $params['id_token'] = $idToken->getToken();
            }

            if ((isset($scope)) && ('' != $scope)) {
                $params['scope'] = $scope;
            }

            if ((isset($state)) && ('' != $state)) {
                $params['state'] = $state;
            }

            $uri_parts['fragment'] = http_build_query($params);
            $redirectUri = OAuthUtils::buildUrl($uri_parts);

            return new HttpRedirectResponse($redirectUri);
        }
    }

    /**
     * Generate a random token string.
     *
     * @return string Random string with 86 chars
     */
    private function createTokenCode()
    {
        $randomData = null;
        if (function_exists('openssl_random_pseudo_bytes')) {
            $strong = false;
            $bytes = openssl_random_pseudo_bytes(100, $strong);
            if ($strong && (false != $bytes)) {
                $randomData = $bytes;
            }
        }
        if (empty($randomData)) {
            $randomData = mt_rand().mt_rand().mt_rand().uniqid(mt_rand(), true).microtime(true).uniqid(mt_rand(), true);
        }
        $hash = hash('sha256', $randomData);
        $code = base64_encode($hash);

        return rtrim(strtr($code, '+/', '-_'), '=');
    }

    /**
     * Handles requests for access-tokens.
     *
     * @param string      $code   The auth-code from the client request
     * @param OAuthClient $client The client requesting this token
     *
     * @return array Returns array with token data
     */
    private function handleAccessTokenRequest($code, OAuthClient $client)
    {
        try {
            $authCode = $this->storage->getAuthCode($code);
        } catch (\Exception $e) {
            throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
        }
        if (is_null($authCode)) {
            throw new OAuthServerException(400, 'invalid_code', 'AuthCode is invalid');
        }
        if ($authCode->hasExpired()) {
            throw new OAuthServerException(400, 'invalid_code', 'AuthCode has expired');
        }
        // Validating the user
        try {
            $user = $this->storage->getUser($authCode->getUserId());
        } catch (\Exception $e) {
            throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
        }
        if (is_null($user)) {
            throw new OAuthServerException(400, 'invalid_code', 'AuthCode is invalid');
        }
        // Validating the Client
        if (GuidUtils::compressGUID($client->getClientId()) !== GuidUtils::compressGUID($authCode->getClientId())) {
            throw new OAuthServerException(400, 'invalid_client', 'Client mismatch');
        }
        $lifetime = $client->getTokenLifetime();
        if ((false == is_numeric($lifetime)) || (($lifetime < 1) && ($lifetime != -1))) {
            $lifetime = 3600;
        }

        if ($lifetime == -1) {
            $expires = -1;
        } else {
            $expires = time() + $lifetime;
        }

        // Create access token
        $accessTokenCode = $this->createTokenCode();
        $refreshTokenCode = $this->createTokenCode();
        $scopeString = $authCode->getScope();
        $scopes = explode(' ', $scopeString);
        try {
            $this->storage->invalidateAuthCode($code);
            $this->storage->createAccessToken($client, $user, $accessTokenCode, $scopeString, $expires);
            $this->storage->createRefreshToken($client, $user, $accessTokenCode, $refreshTokenCode, $scopeString, -1);
        } catch (\Exception $e) {
            throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
        }

        $token = array(
            'access_token' => $accessTokenCode,
            'refresh_token' => $refreshTokenCode,
            'token_type' => 'Bearer',
            'expires_in' => $lifetime,
            'scopes' => $scopes,
        );

        return $token;
    }

    /**
     * Handles requests for refresh-tokens.
     *
     * @param string      $refreshTokenCode The refresh-token from the client request
     * @param OAuthClient $client           The client requesting this token
     * @param string      $scope            The requested scope
     *
     * @return array Returns array with token data
     */
    private function handleRefreshTokenRequest($refreshTokenCode, OAuthClient $client, $scope)
    {
        try {
            $refreshToken = $this->storage->getRefreshToken($refreshTokenCode);
        } catch (\Exception $e) {
            throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
        }
        if (is_null($refreshToken)) {
            throw new OAuthServerException(400, 'invalid_token', 'RefreshToken is invalid');
        }
        if ($refreshToken->hasExpired()) {
            throw new OAuthServerException(400, 'invalid_token', 'RefreshToken has expired');
        }
        // Validating the user
        try {
            $user = $this->storage->getUser($refreshToken->getUserId());
        } catch (\Exception $e) {
            throw new OAuthServerException(500, 'server_error', 'An unknown error occured');
        }
        if (is_null($user)) {
            throw new OAuthServerException(400, 'invalid_token', 'RefreshToken is invalid');
        }
        // Validating the Client
        if (GuidUtils::compressGUID($client->getClientId()) !== GuidUtils::compressGUID($refreshToken->getClientId())) {
            throw new OAuthServerException(400, 'invalid_client', 'Client mismatch');
        }
        $lifetime = $client->getTokenLifetime();
        if ((false == is_numeric($lifetime)) || (($lifetime < 1) && ($lifetime != -1))) {
            $lifetime = 3600;
        }

        if ($lifetime == -1) {
            $expires = -1;
        } else {
            $expires = time() + $lifetime;
        }

        // Create access token
        // Scope from request is ignored here - we are using the initial scope from access-token request
        $accessTokenCode = $this->createTokenCode();
        $scopeString = $refreshToken->getScope();
        $scopes = explode(' ', $scopeString);
        try {
            $this->storage->createAccessToken($client, $user, $accessTokenCode, $scopeString, $expires);
        } catch (\Exception $e) {
            throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
        }

        $token = array(
            'access_token' => $accessTokenCode,
            'token_type' => 'Bearer',
            'expires_in' => $lifetime,
            'scopes' => $scopes,
        );

        return $token;
    }

    /**
     * Token Endpoint as defined in rfc6749 section 3.2.
     *
     * @param HttpRequest $request The incoming http-request
     */
    public function requestToken(HttpRequest $request)
    {
        // only ssl allowed here
        if (($this->forceSsl) && (!$request->isSecure())) {
            throw new OAuthServerException(400, 'invalid_request', 'You have to use ssl to access this endpoint');
        }
        // only POST is supported by this endpoint as defined in the rfc section 3.2
        $params = $request->getPostParams();

        // get the grant type
        $grantType = filter_var($params->get('grant_type', ''), FILTER_SANITIZE_STRING);
        if ('' === $grantType) {
            throw new OAuthServerException(400, 'invalid_request', 'No grant_type supplied');
        }
        if (('authorization_code' !== $grantType) && ('refresh_token' !== $grantType)) {
            throw new OAuthServerException(400, 'invalid_request', 'Invalid grant_type supplied');
        }
        // get the required client_id
        $clientId = filter_var($params->get('client_id', ''), FILTER_SANITIZE_STRING);
        if (0 == strlen($clientId)) {
            throw new OAuthServerException(400, 'invalid_request', 'No client_id supplied');
        }
        // try to find the client by the client_id
        try {
            $client = $this->storage->getClient($clientId);
        } catch (\Exception $e) {
            throw new OAuthServerException(500, 'server_error', 'An unknown error occured', null, $e->getMessage());
        }
        if (is_null($client)) {
            throw new OAuthServerException(400, 'unauthorized_client', 'Client not found: '.$clientId);
        }
        // check if client secret is required
        if ((!is_null($client->getSecret())) && ('' !== $client->getSecret())) {
            $clientSecret = $request->getServerParams()->get('PHP_AUTH_PW', '');
            if ('' == $clientSecret) {
                $clientSecret = $params->get('client_secret', '');
            }
            if ($clientSecret != $client->getSecret()) {
                throw new OAuthServerException(403, 'access_denied', 'Client authentification failed.');
            }
        }

        // handle authorization_code requests
        if ('authorization_code' == $grantType) {
            // get the auth-code
            $code = $params->get('code', '');
            if ('' === $code) {
                throw new OAuthServerException(400, 'invalid_request', 'No code supplied');
            }
            // get the redirect uri
            $redirectUri = filter_var($params->get('redirect_uri', ''), FILTER_SANITIZE_URL);
            if (('' !== $redirectUri) && (false === array_search($redirectUri, $client->getRedirectUris()))) {
                throw new OAuthServerException(403, 'access_denied', 'The redirect URI in the request did not match a registered redirect URI.');
            }
            $token = $this->handleAccessTokenRequest($code, $client);

        // handle refresh_token requests
        } elseif ('refresh_token' == $grantType) {
            $refreshTokenCode = $params->get('refresh_token', '');
            if ('' === $refreshTokenCode) {
                throw new OAuthServerException(400, 'invalid_request', 'No refreshToken supplied');
            }
            $scope = filter_var($params->get('scope', ''), FILTER_SANITIZE_STRING);
            $token = $this->handleRefreshTokenRequest($refreshTokenCode, $client, $scope);
        }

        unset($token['scopes']);

        return new HttpResponse(json_encode($token), 200, OAuthUtils::getResponseHeader());
    }
}
