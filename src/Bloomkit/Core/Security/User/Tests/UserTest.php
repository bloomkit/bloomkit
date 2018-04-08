<?php

namespace Bloomkit\Core\Security\User\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Security\User\User;

class UserTest extends TestCase
{
    public function testConstructor()
    {
        $roles = ['roleA', 'roleB'];
        $user = new User('myUser', $roles);
        $this->assertEquals($user->getUsername(), 'myUser');
        $this->assertEquals($user->getRoles(), $roles);
    }

    public function testInvalidConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User('');
    }

    public function testSetter()
    {
        $user = new User('myUser');
        $user->setUsername('test');
        $this->assertEquals($user->getUsername(), 'test');
        $user->setUserId('1234');
        $this->assertEquals($user->getUserId(), '1234');
        $user->setPolicy('policystring');
        $this->assertEquals($user->getPolicy(), 'policystring');
        $user->setPassword('secret');
        $this->assertEquals($user->getPassword(), 'secret');
        $roles = ['roleA', 'roleB'];
        $user->setRoles($roles);
        $this->assertEquals($user->getRoles(), $roles);
    }
}
