<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Entities\Descriptor\EntityDescriptor;
use Bloomkit\Core\Database\PbxQl\Filter;
use Bloomkit\Core\Entities\Entity;
use Bloomkit\Core\Utilities\Repository;

abstract class AbstractService implements ServiceInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Construktor.
     *
     * @param EntityManager $entityManager EntityManager to use
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById(string $entityDescName, string $dsId): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatasetByFilter(string $entityDescName, string $query): ?Entity
    {
        return null;
    }
    /**
     * {@inheritdoc}
     */
    public function getDatasetById(string $entityDescName, string $dsId): ?Entity
    {
        return null;
    }
    /**
     * {@inheritdoc}
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
    /**
     * {@inheritdoc}
     */
    public function getCount(string $entityDescName, string $query): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(string $entityDescName, string $query, int $limit = 10, int $offset = 0, ?string $orderBy = null, bool $orderAsc = true): Repository
    {
        return new Repository([]);
    }

    protected function getFilterForQuery(EntityDescriptor $entityDesc, string $query): Filter
    {
        if (substr($query, 0, 6) == 'PbxQL:') {
            $subStr = trim(substr($query, 6, strlen($query) - 6));
            if ($subStr !== '') {
                $filter = new Filter($entityDesc, $subStr, $this->entityManager->getDatabaseConnection());
            }
        } else {
            $filter = new Filter($entityDesc, '* like "%'.$query.'%"', $this->entityManager->getDatabaseConnection());
        }

        return $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(string $entityDescName, array $data): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function updateByFilter(string $entityDescName, string $query, array $data): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateById(string $entityDescName, string $dsId, array $data): bool
    {
        return false;
    }
}
