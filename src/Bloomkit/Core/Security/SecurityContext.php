<?php

namespace Bloomkit\Core\Security;

use Bloomkit\Core\Security\Token\Token;

/**
 * Representation of the applications security-context.
 */
class SecurityContext
{
    private $token;

    /**
     * Returns the authentication-token.
     *
     * @return Token|null The authentication token or null if not set
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the authentication-token.
     *
     * @param Token $token The authentication token to set
     */
    public function setToken(Token $token = null)
    {
        $this->token = $token;
    }
}
