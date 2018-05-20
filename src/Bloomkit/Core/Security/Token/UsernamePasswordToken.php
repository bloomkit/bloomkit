<?php

namespace Bloomkit\Core\Security\Token;

/**
 * Representation of a username/password token.
 */
class UsernamePasswordToken extends Token
{
    /**
     * @var string
     */
    private $password;

    /**
     * Constructor.
     *
     * @param string $username The name of the user
     * @param string $password The password of the user
     * @param array  $roles    The roles to set
     */
    public function __construct($username, $password, array $roles = [])
    {
        parent::__construct($roles);
        $this->setUserName($username);
        $this->password = $password;
    }

    /**
     * Returns the password of the user.
     *
     * @return string The password of the user
     */
    public function getPassword()
    {
        return $this->password;
    }
}
