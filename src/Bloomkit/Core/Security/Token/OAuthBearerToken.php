<?php

namespace Bloomkit\Core\Security\Token;

/**
 * Representation of a OAuth Bearer token.
 */
class OAuthBearerToken extends Token
{
    /**
     * @var string
     */
    private $bearerToken;

    /**
     * Constructor.
     *
     * @param string $bearerToken The access token
     * @param array  $roles       The roles to set
     */
    public function __construct($bearerToken, array $roles = [])
    {
        parent::__construct($roles);
        $this->bearerToken = $bearerToken;
    }

    /**
     * Returns the bearer token.
     *
     * @return string The bearer token
     */
    public function getBearerToken()
    {
        return $this->bearerToken;
    }
}
