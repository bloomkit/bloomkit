<?php

namespace Bloomkit\Core\Security\User;

/**
 * Defines how an UserProvicer object should look like.
 */
interface UserProviderInterface
{
    /**
     * Returns a user by a username.
     *
     * @param string $username The username of the user to load
     *
     * @return UserInterface|null Returns the matching user or null if not found
     */
    public function loadUserByUsername($username);
}
