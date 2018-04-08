<?php

namespace Bloomkit\Core\Auth\OAuth2;

/**
 * Defines how a OAuthClient object should look like.
 */
interface OAuthClientInterface
{
    /**
     * Returns the id of the client.
     *
     * @return string The client id
     */
    public function getClientId();

    /**
     * Returns the registered redirect URIs.
     *
     * @return array The redirect URIs
     */
    public function getRedirectUris();
}
