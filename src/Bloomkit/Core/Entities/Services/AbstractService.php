<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Entities\Descriptor\EntityDescriptor;
use Bloomkit\Core\Database\PbxQl\Filter;

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
    public function deleteById($entityDescName, $dsId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDatasetByFilter($entityDescName, $query)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDatasetById($entityDescName, $dsId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($entityDescName, $query)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getList($entityDescName, $query, $limit = 10, $offset = 0, $orderBy = null, $orderAsc = true)
    {
    }

    /**
     * Create a filter object for a query.
     *
     * @param EntityDescriptor $entityDesc The entity descriptor to use
     * @param string           $query      The query to use
     */
    protected function getFilterForQuery(EntityDescriptor $entityDesc, $query)
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
    public function insert($entityDescName, $data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateByFilter($entityDescName, $query, $data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateById($entityDescName, $dsId, $data)
    {
    }
}
