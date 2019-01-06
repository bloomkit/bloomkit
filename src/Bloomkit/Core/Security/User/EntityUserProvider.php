<?php

namespace Bloomkit\Core\Security\User;

use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Application\Application;

/**
 * Abstract class for providing users by the EntityManager.
 */
abstract class EntityUserProvider implements UserProviderInterface
{
    protected $application;

    /**
     * Constuctor.
     *
     * @param Application $application The application object
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Constuctor.
     *
     * @return EntityManager The EntityManager object
     */
    public function getEntityManager()
    {
        return $this->application->getEntityManager();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function loadUserByUsername($username);
}
