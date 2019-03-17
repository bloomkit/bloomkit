<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Database\PbxQl\Filter;
use Bloomkit\Core\Entities\Entity;
use Bloomkit\Core\Utilities\Repository;

class CrudService extends AbstractService
{
    /**
     * {@inheritdoc}
     */
	public function deleteById(string $entityDescName, string $dsId): bool
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);
        $entity = $this->entityManager->loadById($entityDesc, $dsId);
        if ($entity === false) {
            return false;
        }
        $this->entityManager->delete($entity);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatasetByFilter(string $entityDescName, string $query): ?Entity
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);
        $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        $entity = $this->entityManager->load($entityDesc, $filter);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatasetById(string $entityDescName, string $dsId): ?Entity
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);

        return $this->entityManager->loadById($entityDesc, $dsId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(string $entityDescName, ?string $query): int
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);
        $filter = null;
        if (!empty($query)) {
            $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        }

        return $this->entityManager->getCount($entityDesc, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(string $entityDescName, ?string $query, int $limit = 10, int $offset = 0, ?string $orderBy = null, bool $orderAsc = true): Repository
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);
        $filter = null;
        if (!empty($query)) {
            $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        }

        return $this->entityManager->loadList($entityDesc, $filter, $limit, $offset, $orderBy, $orderAsc);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(string $entityDescName, array $data): string
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);
        $entity = new Entity($entityDesc);

        foreach ($data as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }

        return $this->entityManager->insert($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function updateByFilter(string $entityDescName, string $query, array $data): bool
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);

        $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        $entity = $this->entityManager->load($entityDesc, $filter);
        if ($entity === false) {
            return false;
        }

        foreach ($data as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }

        $this->entityManager->update($entity);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateById(string $entityDescName, string $dsId, array $data): bool
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);

        $entity = $this->entityManager->loadById($entityDesc, $dsId);
        if ($entity === false) {
            return false;
        }

        foreach ($data as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }
        $this->entityManager->update($entity);

        return true;
    }
}
