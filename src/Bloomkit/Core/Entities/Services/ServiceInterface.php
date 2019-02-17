<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Entities\Entity;

/**
 * Describes all functions a service implementation must provide.
 */
interface ServiceInterface
{
    /**
     * Delete a specific entitiy by its id.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param string $id             The id of the entity to delete
     *
     * @return bool True on success false if dataset is not found
     */
    public function deleteById($entityDescName, $dsId);

    /**
     * Load a specific entitiy by filter query.
     *
     * @param string $entityDescName The name of the entity Class to use
     * @param string $query          A PbxQl Query to use
     *
     * @return Entity|false The matching entity or false if not found
     */
    public function getDatasetByFilter($entityDescName, $query);

    /**
     * Load a specific entitiy by its id.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param string $id             The id of the Entity to load
     *
     * @return Entity|false The matching entity or false if not found
     */
    public function getDatasetById($entityDescName, $dsId);

    /**
     * Returns the entity manager.
     *
     * @return EntityManager The entity manager of the application
     */
    public function getEntityManager();

    /**
     * Returns the rowCount for a given entityDescriptor and an optional filter.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param string $query          A PbxQl Query to use
     *
     * @return int The total count of datasets
     */
    public function getCount($entityDescName, $query);

    /**
     * Load a list of Entities.
     *
     * @param string      $entityDescName The name of the Entity Class to use
     * @param string      $query          A PbxQl Query to use
     * @param int         $limit          The amount of entities to load
     * @param int         $offset         The offset to start loading entities from
     * @param string|null $orderBy        The id of the field to order by
     * @param bool        $orderAsc       Order ascending if true, descending if false
     *
     * @return Repository A Repository containing the loaded entities
     */
    public function getList($entityDescName, $query, $limit = 10, $offset = 0, $orderBy = null, $orderAsc = true);

    /**
     * Insert a dataset with the provided data.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param array  $data           Associative array of values (fieldId->fieldValue)
     *
     * @return string The id of the saved entity
     */
    public function insert($entityDescName, $data);

    /**
     * Update a single dataset by a filter.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param array  $data           Associative array of values (fieldId->fieldValue)
     *
     * @return bool True on success false if dataset is not found
     */
    public function updateByFilter($entityDescName, $query, $data);

    /**
     * Update a single dataset by the id.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param array  $data           Associative array of values (fieldId->fieldValue)
     *
     * @return bool True on success false if dataset is not found
     */
    public function updateById($entityDescName, $dsId, $data);
}
