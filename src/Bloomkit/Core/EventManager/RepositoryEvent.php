<?php

namespace Bloomkit\Core\EventManager;

use Bloomkit\Core\Utilities\Repository;

class RepositoryEvent extends Event
{
    /**
     * @var Repository
     */
    protected $repository;

    public function __construct()
    {
        $this->repository = new Repository();
    }

    /**
     * Return the repository.
     *
     * @result Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
