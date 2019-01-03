<?php

namespace Bloomkit\Core\Security\OAuth2\Tests\Mockups;

use Bloomkit\Core\Security\User\UserInterface;

class MockUser implements UserInterface
{
    private $userId;

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getRoles()
    {
    }

    public function getPassword()
    {
    }

    public function getScopes()
    {
        return ['myscope'];
    }

    public function getUsername()
    {
    }

    public function setUsername($username)
    {
    }

    public function setPassword($password)
    {
    }

    public function setRoles(array $roles)
    {
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function validatePassword($password, $pepper = '')
    {
    }
}
