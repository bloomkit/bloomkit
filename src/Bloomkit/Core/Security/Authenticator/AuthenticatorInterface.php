<?php

namespace Bloomkit\Core\Security\Authenticator;

use Bloomkit\Core\Security\User\UserProviderInterface;
use Bloomkit\Core\Security\Token\Token;
use Bloomkit\Core\Http\HttpRequest;

/**
 * Defines how an authenticator should look like.
 */
interface AuthenticatorInterface
{
    /**
     * Find and authenticate a user for this token.
     *
     * @param Token                 $token        The token to authenticate
     * @param UserProviderInterface $userProvider UserProvider to check for a matching user
     * @param array                 $options      Array of custom options
     *
     * @return Token Returns the token
     *
     * @throws Exception Throws an exeception if something went wrong
     */
    public function authenticateToken(Token $token, UserProviderInterface $userProvider, $options = []);

    /**
     * Creates a token for a HttpRequest and returns it.
     *
     * @param HttpRequest $request The http request to create a token for
     *
     * @return Token The token for the request
     *
     * @throws Exception Throws an exeception if something went wrong
     */
    public function createToken(HttpRequest $request);

    /**
     * Check if the authenticator supports a specific token.
     *
     * @param Token $token The token to check
     *
     * @return bool True if the token is supported, false if not
     */
    public function supportsToken(Token $token);
}
