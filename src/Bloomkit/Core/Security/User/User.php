<?php

namespace Bloomkit\Core\Security\User;

/**
 * Representation of an user object.
 */
class User implements UserInterface
{
    /**
     * @var string
     */
    protected $password;

    /**
     * @var mixed
     */
    protected $policy;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var string
     */
    protected $username;

    /**
     * Constuctor.
     *
     * @param string $username The name of the user
     * @param array  $roles    An array of role-names for the user
     */
    public function __construct($username, array $roles = [])
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }
        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the users policy.
     *
     * @return mixed The users policy
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Set a policy for the user.
     *
     * @param mixed $policy The user-policy
     */
    public function setPolicy($policy)
    {
        $this->policy = $policy;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword($password, $pepper = '')
    {
        $hash = hash_hmac('sha512', $password, $pepper, true);
        $a = crypt($hash, substr($this->password, 0, 30));

        return $a == $this->password;
    }
}
