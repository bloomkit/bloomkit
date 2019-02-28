<?php

namespace Bloomkit\Core\Security\User;

/**
 * Defines how an user object should look like.
 */
interface UserInterface
{
    /**
     * Returns the instance_id if set (used for multi client setups).
     *
     * @return string The instance_id
     */
    public function getInstanceId();

    /**
     * Returns the users password.
     *
     * @return string The users password
     */
    public function getPassword();

    /**
     * Returns an array of user roles.
     *
     * @return array An array of user roles
     */
    public function getRoles();

    /**
     * Returns the scopes of the user.
     *
     * @return string The users scopes
     */
    public function getScopes();

    /**
     * Returns the id of the user.
     *
     * @return string The users id
     */
    public function getUserId();

    /**
     * Returns the name of the user.
     *
     * @return string The username
     */
    public function getUsername();

    /**
     * Set the instance id (used for multi client setups).
     *
     * @param string $instanceId
     */
    public function setInstanceId(string $instanceId);

    /**
     * Set the password of the user.
     *
     * @param string $password The users password
     */
    public function setPassword($password);

    /**
     * Set the users roles.
     *
     * @param array $roles The users roles
     */
    public function setRoles(array $roles);

    /**
     * Set the id of the user.
     *
     * @param string $userId The id of the user
     */
    public function setUserId($userId);

    /**
     * Set the username.
     *
     * @param string $username The username
     */
    public function setUsername($username);

    /**
     * Check if the given password and pepper matches the users password.
     *
     * @param string $password The password to verify
     *
     * @return string $pepper An optional pepper to use for the verification
     * @return bool   true if password match, false if not
     */
    public function validatePassword($password);
}
