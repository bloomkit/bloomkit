<?php

namespace Bloomkit\Core\Security\Token;

use Bloomkit\Core\Security\User\UserInterface;

/**
 * Representation of a generic token.
 */
class Token
{
    /**
     * @var bool
     */
    private $authenticated;

    /**
     * @var bool
     */
    private $isStateful;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var string
     */
    private $username;

    /**
     * Constructor.
     *
     * @param array $roles The roles to set
     */
    public function __construct(array $roles = [])
    {
        $this->roles = $roles;
        $this->authenticated = false;
        $this->isStateful = true;
    }

    /**
     * Returns the roles.
     *
     * @return array The roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the user object if set.
     *
     * @return UserInterface|null Returns the user object or null if not set
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Returns the name of the user.
     *
     * @return string The name of the user
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the authenticated flag.
     *
     * @param bool True if the token is authenticated, false if not
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Returns the stateful flag.
     *
     * @return bool True if the token is stateful, false if not
     */
    public function isStateful()
    {
        return $this->isStateful;
    }

    /**
     * Set the authenticated flag.
     *
     * @param bool $isAuthenticated True if the token is authenticated, false if not
     */
    public function setAuthenticated($isAuthenticated)
    {
        $this->authenticated = (bool) $authenticated;
    }

    /**
     * Set the stateful flag.
     *
     * @param bool $isStateful True if the token is stateful, false if not
     */
    public function setStateful($isStateful)
    {
        $this->isStateful = (bool) $isStateful;
    }

    /**
     * Set the user object.
     *
     * @param UserInterfact $user The user object to set
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Set the name of the user.
     *
     * @param string $username The name of the user
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}
