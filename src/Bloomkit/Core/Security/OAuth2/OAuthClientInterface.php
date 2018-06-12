<?php

namespace Bloomkit\Core\Security\OAuth2;

/**
 * Defines how an OAuthClient object should look like.
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
