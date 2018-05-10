<?php

namespace Bloomkit\Core\Security\OAuth2;

use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Http\HttpResponse;
use Bloomkit\Core\Http\HttpRedirectResponse;
use Bloomkit\Core\Security\OAuth2\Exceptions\OAuthServerException;
use Bloomkit\Core\Security\OAuth2\Exceptions\OAuthRedirectException;

/**
 * Helper functions for OAuth-Server.
 */
class OAuthUtils
{
    /**
     * Get url as string from an array of url-parts.
     *
     * @param array $urlParts The url parts to build the url
     *
     * @return string
     */
    public static function buildUrl(array $urlParts)
    {
        $url = $urlParts['scheme'].'://';
        if (isset($urlParts['user'])) {
            $url .= $urlParts['user'];
            if (isset($urlParts['pass'])) {
                $url .= ':'.$urlParts['pass'];
            }
            $url .= '@';
        }
        $url .= $urlParts['host'];
        if (isset($urlParts['port'])) {
            $url .= ':'.$urlParts['port'];
        }
        if (isset($urlParts['path'])) {
            $url .= $urlParts['path'];
        }
        if (isset($urlParts['query'])) {
            $url .= '?'.$urlParts['query'];
        }
        if (isset($urlParts['fragment'])) {
            $url .= '#'.$urlParts['fragment'];
        }

        return $url;
    }

    /**
     * Extract bearer token from http-request.
     *
     * @param HttpRequest $request The request to check for bearer token
     *
     * @return string The token string
     */
    public static function getBearerTokenFromRequest(HttpRequest $request)
    {
        $token = $request->getHeaders()->get('Authorization', null);
        if ((is_null($token)) || (empty($token))) {
            $token = $request->getHeaders()->get('HTTP_AUTHORIZATION', null);
        }
        if (is_null($token)) {
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                if ((is_array($headers)) && (isset($headers['Authorization']))) {
                    $token = $headers['Authorization'];
                }
            }
        }

        if (is_null($token)) {
            return null;
        }

        if (!preg_match('/'.preg_quote('Bearer', '/').'\s(\S+)/', $token, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Create Response / Redirect based on an exeption.
     *
     * @param Exception $e The exception to handle
     *
     * @return mixed
     */
    public static function getResponseForException(\Exception $e)
    {
        $code = 400;
        $error = $e->getMessage();
        $uri = '';
        $desc = '';

        $header = self::getResponseHeader();

        if ($e instanceof OAuthRedirectException) {
            $redirectUri = trim(filter_var($e->getRedirectUri(), FILTER_SANITIZE_URL));
            $uri_parts = parse_url($redirectUri);
            $params = [];
            $params['error'] = $e->getMessage();
            if ((!is_null($e->getDescription())) && ('' != $e->getDescription())) {
                $params['error_description'] = $e->getDescription();
            }
            if ((!is_null($e->getUri())) && ('' != $e->getUri())) {
                $params['error_uri'] = $e->getUri();
            }
            if ((!is_null($e->getState())) && ('' != $e->getState())) {
                $params['state'] = $e->getState();
            }

            $uri_parts['fragment'] = http_build_query($params);
            $redirectUri = OAuthUtils::buildUrl($uri_parts);

            return new HttpRedirectResponse($redirectUri, 302, $header);
        }

        if ($e instanceof OAuthServerException) {
            $code = $e->getHttpCode();
            $desc = trim($e->getDescription());
            $uri = trim(filter_var($e->getUri(), FILTER_SANITIZE_URL));
        }

        $errorDoc = array();
        $errorDoc['error'] = $error;
        if ('' !== $desc) {
            $errorDoc['error_description'] = $desc;
        }
        if ('' !== $uri) {
            $errorDoc['error_uri'] = $uri;
        }

        $message = json_encode($errorDoc);

        return new HttpResponse($message, $code, $header);
    }

    /**
     * Return no-caching header for OAuthServer Responses.
     *
     * @return array
     */
    public static function getResponseHeader()
    {
        $header = array(
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
        );

        return $header;
    }
}
