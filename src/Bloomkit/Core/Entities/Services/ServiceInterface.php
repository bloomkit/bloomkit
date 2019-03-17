<?php

namespace Bloomkit\Core\Entities\Services;

use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Entities\Entity;
use Bloomkit\Core\Utilities\Repository;

interface ServiceInterface
{
    public function deleteById(string $entityDescName, string $dsId): bool;
    public function getDatasetByFilter(string $entityDescName, string $query): ?Entity;
    public function getDatasetById(string $entityDescName, string $dsId): ?Entity;
    public function getEntityManager(): EntityManager;
    public function getCount(string $entityDescName, string $query): int;
    public function getList(string $entityDescName, string $query, int $limit = 10, int $offset = 0, ?string $orderBy = null, bool $orderAsc = true): Repository;

    /**
     * @param $data Associative array of values (fieldId->fieldValue)
     * @return string The id of the saved entity
     */
    public function insert(string $entityDescName, array $data): string;

    /**
     * Update a single dataset by a filter.
     *
     * @param $data Associative array of values (fieldId->fieldValue)
     */
    public function updateByFilter(string $entityDescName, string $query, array $data): bool;

    /**
     * Update a single dataset by the id.
     *
     * @param $data Associative array of values (fieldId->fieldValue)
     */
    public function updateById(string $entityDescName, string $dsId, array $data): bool;
}