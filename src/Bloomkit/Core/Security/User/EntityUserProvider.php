<?php

namespace Bloomkit\Core\Security\User;

use Bloomkit\Core\Entities\EntityManager;

/**
 * Abstract class for providing users by the EntityManager.
 */
abstract class EntityUserProvider implements UserProviderInterface
{
    protected $entityManager;

    /**
     * Constuctor.
     *
     * @param EntityManager $entityManager The name of the user
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Constuctor.
     *
     * @return EntityManager The EntityManager object
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function loadUserByUsername($username);
}
