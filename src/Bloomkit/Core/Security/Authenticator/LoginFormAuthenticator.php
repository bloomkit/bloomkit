<?php

namespace Bloomkit\Core\Security\Authenticator;

use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Security\Token\Token;
use Bloomkit\Core\Security\Token\UsernamePasswordToken;
use Bloomkit\Core\Security\User\UserProviderInterface;
use Bloomkit\Core\Security\Exceptions\BadCredentialsException;
use Bloomkit\Core\Security\Exceptions\AuthFailedException;
use Bloomkit\Core\Security\Exceptions\CredentialsMissingException;

/**
 * Authenticator for LoginForms.
 */
class LoginFormAuthenticator implements AuthenticatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function authenticateToken(Token $token, UserProviderInterface $userProvider)
    {
        if (is_null($token)) {
            throw new AuthFailedException('No token provided');
        }
        $username = $token->getUsername();
        $password = $token->getPassword();

        $user = $userProvider->loadUserByUsername($username);
        if (is_null($user)) {
            throw new BadCredentialsException('Invalid username or password');
        }
        if (false == $user->validatePassword($password)) {
            throw new BadCredentialsException('Invalid username or password');
        }
        $token->setUser($user);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(HttpRequest $request)
    {
        $username = trim($request->getPostParams()->get('login'), null);
        $password = $request->getPostParams()->get('password', null);
        if ((is_null($username)) || (is_null($password))) {
            throw new CredentialsMissingException('No credentials found in request');
        }

        return new UsernamePasswordToken($username, $password);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(Token $token)
    {
        return $token instanceof UsernamePasswordToken;
    }
}
