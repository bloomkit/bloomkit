<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Database\PbxQl\Filter;
use Bloomkit\Core\Entities\Entity;
use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Exceptions\NotFoundException;

class CrudService implements CrudServiceInterface
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
    public function requireDatasetByFilter(string $entityDescName, string $query): Entity
    {
        $entity = $this->getDatasetByFilter($entityDescName, $query);
        if(is_null($entity)) {
            throw new NotFoundException("Not found");
        }

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
    public function requireDatasetById(string $entityDescName, string $dsId): Entity
    {
        $entity = $this->getDatasetById($entityDescName, $dsId);
        if(is_null($entity)) {
            throw new NotFoundException("Not found '$dsId");
        }

        return $entity;
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
    public function getList(string $entityDescName, ?string $query, ?ListOutputParameters $params = null): ListResult
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);
        $params = $params ?? new ListOutputParameters();
        $filter = null;
        if (!empty($query)) {
            $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        }

        $repository = $this->entityManager->loadList($entityDesc, $filter, $params->limit, $params->offset, $params->orderBy, $params->orderAsc);

        $count = null;
        if($params->determineTotalCount === true) {
            $count = $this->entityManager->getCount($entityDesc, $filter);
        }

        return new ListResult($repository->getItems(), $count);
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

    /**
     * {@inheritdoc}
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
