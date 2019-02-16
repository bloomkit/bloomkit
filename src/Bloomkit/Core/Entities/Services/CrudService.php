<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Database\PbxQl\Filter;
use Bloomkit\Core\Entities\Entity;

class CrudService extends AbstractService
{
    /**
     * {@inheritdoc}
     */
    public function deleteById($entityDescName, $dsId)
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
    public function getDatasetByFilter($entityDescName, $query)
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);
        $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        $entity = $this->entityManager->load($entityDesc, $filter);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatasetById($entityDescName, $dsId)
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);

        return $this->entityManager->loadById($entityDesc, $dsId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($entityDescName, $query)
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
    public function getList($entityDescName, $query, $limit = 10, $offset = 0, $orderBy = null, $orderAsc = true)
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
    public function insert($entityDescName, $data)
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
    public function updateByFilter($entityDescName, $query, $data)
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);

        $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        $entity = $this->entityManager->load($entityDesc, $filter);
        if ($entity === false) {
            return false;
        }

        foreach ($requestData as $key => $value) {
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
    public function updateById($entityDescName, $dsId, $data)
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);

        $entity = $this->entityManager->loadById($entityDesc, $dsId);
        if ($entity === false) {
            return false;
        }

        foreach ($requestData as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }
        $this->entityManager->update($entity);

        return true;
    }
}
