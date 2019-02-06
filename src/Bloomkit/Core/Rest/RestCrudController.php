<?php

namespace Bloomkit\Core\Rest;

use Bloomkit\Core\Module\Controller;
use Bloomkit\Core\Database\PbxQl\Filter;
use Bloomkit\Core\Entities\Entity;
use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Rest\Exceptions\RestFaultException;
use Bloomkit\Core\Exceptions\NotFoundException;

class RestCrudController extends Controller
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $entityDescName;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    public function deleteById($dsId)
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($this->entityDescName);
        $entity = $this->entityManager->loadById($entityDesc, $dsId);

        if (false == $entity) {
            return RestResponse::createFault(404, 'Not Found', 404);
        }

        $this->entityManager->delete($entity);
        $result['success'] = true;

        return new RestResponse(json_encode($result), 200);
    }

    /**
     * @param array $dsIds
     */
    public function bulkDelete($dsIds)
    {
        $this->bulkOperation($dsIds, function ($dsId) {
            $this->deleteById($dsId);
        });
    }

    public function getDatasetByFilter($query)
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($this->entityDescName);
        $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        $entities = $this->entityManager->loadList($entityDesc, $filter, 1);
        $entities = $entities->getItems();
        if (1 != count($entities)) {
            return false;
        }

        return reset($entities);
    }

    public function getDatasetById($dsId)
    {
        $entityDesc = $this->entityManager->getEntityDescriptor($this->entityDescName);
        $entity = $this->entityManager->loadById($entityDesc, $dsId);
        if (false == $entity) {
            throw new NotFoundException(sprintf('"%s" not found', $dsId));
        }

        return $entity;
    }

    public function getList($filter = null, $entityDescName = null)
    {
        if (!isset($entityDescName)) {
            $entityDescName = $this->entityDescName;
        }

        $request = $this->getRequest();
        $params = $request->getGetParams();
        $limit = (int) $params->get('limit', 20);
        $offset = (int) $params->get('offset', 0);
        $orderAsc = (bool) $params->get('orderAsc', true);
        $orderBy = $params->get('orderBy', null);

        $entityDesc = $this->entityManager->getEntityDescriptor($entityDescName);

        if (is_null($filter)) {
            $filterStr = $params->get('filter');
            if (isset($filterStr)) {
                if (substr($filterStr, 0, 6) == 'PbxQL:') {
                    $subStr = trim(substr($filterStr, 6, strlen($filterStr) - 6));
                    if ($subStr !== '') {
                        $filter = new Filter($entityDesc, $subStr, $this->entityManager->getDatabaseConnection());
                    }
                } else {
                    $filter = new Filter($entityDesc, '* like "%'.$filterStr.'%"', $this->entityManager->getDatabaseConnection());
                }
            }
        }

        $entitites = $this->entityManager->loadList($entityDesc, $filter, $limit, $offset, $orderBy, $orderAsc);
        $count = $this->entityManager->getCount($entityDesc, $filter);
        $response = new RestResponse();
        $response->setEntityList($entitites, $count);
        $response->setStatusCode(200);

        return $response;
    }

    public function insert()
    {
        $request = $this->getRequest();
        $requestData = $request->getJsonData();

        if (is_null($requestData)) {
            return RestResponse::createFault(400, 'Invalid request: No JSON found.');
        }

        $entityDesc = $this->entityManager->getEntityDescriptor($this->entityDescName);
        $entity = new Entity($entityDesc);

        foreach ($requestData as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }

        $this->entityManager->insert($entity);

        $result['success'] = true;
        $result['id'] = $entity->getDatasetId();

        return new RestResponse(json_encode($result), 200);
    }

    public function updateByFilter($query, $maxItems = 1)
    {
        $request = $this->getRequest();
        $requestData = $request->getJsonData();

        if (is_null($requestData)) {
            return RestResponse::createFault(400, 'Invalid request: No JSON found.');
        }

        $entityDesc = $this->entityManager->getEntityDescriptor($this->entityDescName);
        $filter = new Filter($entityDesc, $query, $this->entityManager->getDatabaseConnection());
        $entities = $this->entityManager->loadList($entityDesc, $filter, 1);
        $entities = $entities->getItems();

        if (1 != count($entities)) {
            return RestResponse::createFault(404, 'Not Found', 404);
        }

        $entity = reset($entities);

        foreach ($requestData as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }

        $this->entityManager->update($entity);
        $result['success'] = true;

        return new RestResponse(json_encode($result), 200);
    }

    public function updateById($dsId)
    {
        $request = $this->getRequest();
        $requestData = $request->getJsonData();

        if (is_null($requestData)) {
            return RestResponse::createFault(400, 'Invalid request: No JSON found.');
        }

        $entityDesc = $this->entityManager->getEntityDescriptor($this->entityDescName);
        $entity = $this->entityManager->loadById($entityDesc, $dsId);

        if (false == $entity) {
            return RestResponse::createFault(404, 'Not Found', 404);
        }

        foreach ($requestData as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }
        $this->entityManager->update($entity);
        $result['success'] = true;

        return new RestResponse(json_encode($result), 200);
    }

    /**
     * @param array    $dsIds
     * @param \Closure $operationForSingleId
     */
    protected function bulkOperation($dsIds, $operationForSingleId)
    {
        $failedIds = [];
        foreach ($dsIds as $id) {
            try {
                $operationForSingleId($id);
            } catch (\Exception $th) {
                $failedIds[] = $id;
            }
        }

        if (count($failedIds) > 0) {
            throw new RestFaultException(500, 'Following datasets could not be processed: '.implode(', ', $failedIds));
        }
    }
}
