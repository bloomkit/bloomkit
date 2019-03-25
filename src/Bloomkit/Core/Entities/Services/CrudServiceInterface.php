<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Entities\Entity;
use Bloomkit\Core\Utilities\Repository;

/**
 * Describes all functions a service implementation must provide.
 */
interface CrudServiceInterface
{
    /**
     * Delete a specific entitiy by its id.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param string $id             The id of the entity to delete
     *
     * @return bool True on success false if dataset is not found
     */
	public function deleteById(string $entityDescName, string $dsId): bool;

    /**
     * Load a specific entitiy by filter query.
     *
     * @param string $entityDescName The name of the entity Class to use
     * @param string $query          A PbxQl Query to use
     *
     * @return Entity|false The matching entity or false if not found
     */
    public function getDatasetByFilter(string $entityDescName, string $query): ?Entity;

    /**
     * Load a specific entitiy by its id.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param string $id             The id of the Entity to load
     *
     * @return Entity|false The matching entity or false if not found
     */
    public function getDatasetById(string $entityDescName, string $dsId): ?Entity;

    /**
     * Returns the entity manager.
     *
     * @return EntityManager The entity manager of the application
     */
    public function getEntityManager(): EntityManager;

    /**
     * Returns the rowCount for a given entityDescriptor and an optional filter.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param string $query          A PbxQl Query to use
     *
     * @return int The total count of datasets
     */
    public function getCount(string $entityDescName, string $query): int;

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
    public function getList(string $entityDescName, string $query, int $limit = 10, int $offset = 0, ?string $orderBy = null, bool $orderAsc = true): Repository;

    /**
     * Insert a dataset with the provided data.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param array  $data           Associative array of values (fieldId->fieldValue)
     *
     * @return string The id of the saved entity
     */
    public function insert(string $entityDescName, array $data): string;

    /**
     * Update a single dataset by a filter.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param array  $data           Associative array of values (fieldId->fieldValue)
     *
     * @return bool True on success false if dataset is not found
     */
    public function updateByFilter(string $entityDescName, string $query, array $data): bool;

    /**
     * Update a single dataset by the id.
     *
     * @param string $entityDescName The name of the Entity Class to use
     * @param array  $data           Associative array of values (fieldId->fieldValue)
     *
     * @return bool True on success false if dataset is not found
     */
	public function updateById(string $entityDescName, string $dsId, array $data): bool;
}
