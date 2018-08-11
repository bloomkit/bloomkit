<?php
namespace Bloomkit\Core\Security\User;

use Bloomkit\Core\EventManager\Event;

/**
 * Event concerning User actions.
 */
class UserEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * Constructor
     *
     * @param User $user The User object this event is about
     */
    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }
    
    /**
     * Return the User object
     * 
     * @return User The User object of this event
     */
    public function getUser()
    {
        return $this->user;
    }

}
