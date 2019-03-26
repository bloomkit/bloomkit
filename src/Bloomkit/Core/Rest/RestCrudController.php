<?php

namespace Bloomkit\Core\Rest;

use Bloomkit\Core\Module\Controller;
use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Rest\Exceptions\RestFaultException;
use Bloomkit\Core\Entities\Services\CrudServiceInterface;
use Bloomkit\Core\Entities\Services\CrudService;
use Bloomkit\Core\Entities\Services\ListOutputParameters;
use Bloomkit\Core\Utilities\Repository;

class RestCrudController extends Controller
{
    /**
     * @var CrudServiceInterface
     */
    protected $service;

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
        $this->service = new CrudService($entityManager);
    }

    public function deleteById($dsId)
    {
        $result = $this->service->deleteById($this->entityDescName, $dsId);
        if (!$result) {
            return RestResponse::createFault(404, 'Not Found', 404);
        } else {
            return new RestResponse(json_encode(['success' => true]), 200);
        }
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
        $entity = $this->service->getDatasetByFilter($this->entityDescName, $query);
        if ($entity === false) {
            return RestResponse::createFault(404, 'Not Found', 404);
        } else {
            $restResponse = new RestResponse();
            $restResponse->setEntity($entity);
            $restResponse->setStatusCode(200);

            return $restResponse;
        }
    }

    public function getDatasetById($dsId)
    {
        $entity = $this->service->getDatasetById($this->entityDescName, $dsId);
        if ($entity === false) {
            return RestResponse::createFault(404, sprintf('"%s" not found', $dsId), 404);
        } else {
            $restResponse = new RestResponse();
            $restResponse->setEntity($entity);
            $restResponse->setStatusCode(200);

            return $restResponse;
        }
    }

    public function getList($filter = null, $entityDescName = null)
    {
        if (!isset($entityDescName)) {
            $entityDescName = $this->entityDescName;
        }

        if (isset($filter)) {
            $filterStr = $filter->getPbxQlQuery();
        } else {
            $filterStr = $this->createFilterStringFromRequest();
        }

        $listParams = $this->createListOutputParametersFromRequest();
        $entities = $this->service->getList($entityDescName, $filterStr, $listParams);
        $count = $this->service->getCount($entityDescName, $filterStr);

        return $this->createEntityListResponse($entities, $count);
    }

    protected function createListOutputParametersFromRequest(): ListOutputParameters
    {
        $request = $this->getRequest();
        $params = $request->getGetParams();

        $result = new ListOutputParameters();
        $result->limit = (int) $params->get('limit', 20);
        $result->offset = (int) $params->get('offset', 0);
        $result->orderAsc = (bool) $params->get('orderAsc', true);
        $result->orderBy = $params->get('orderBy', null);
        return $result;
    }

    protected function createFilterStringFromRequest(): ?string
    {
        $request = $this->getRequest();
        $params = $request->getGetParams();
        $filterStr = $params->get('filter');

        if (isset($filterStr)) {
            if (substr($filterStr, 0, 6) == 'PbxQL:') {
                $filterStr = trim(substr($filterStr, 6, strlen($filterStr) - 6));
            } else {
                $filterStr = '* like "%'.$filterStr.'%"';
            }
        }

        return $filterStr;
    }

    protected function createEntityListResponse(Repository $entities, ?int $count = null)
    {
        $response = new RestResponse();
        $response->setEntityList($entities, $count);
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

        $dsId = $this->service->insert($this->entityDescName, $requestData);

        $result['success'] = true;
        $result['id'] = $dsId;

        return new RestResponse(json_encode($result), 200);
    }

    public function updateByFilter($query)
    {
        $request = $this->getRequest();
        $requestData = $request->getJsonData();

        if (is_null($requestData)) {
            return RestResponse::createFault(400, 'Invalid request: No JSON found.');
        }

        $result = $this->service->updateByFilter($this->entityDescName, $query, $requestData);
        if (!$result) {
            return RestResponse::createFault(404, 'Not Found', 404);
        } else {
            return new RestResponse(json_encode(['success' => true]), 200);
        }
    }

    public function updateById($dsId)
    {
        $request = $this->getRequest();
        $requestData = $request->getJsonData();

        if (is_null($requestData)) {
            return RestResponse::createFault(400, 'Invalid request: No JSON found.');
        }

        $result = $this->service->updateById($this->entityDescName, $dsId, $requestData);
        if (!$result) {
            return RestResponse::createFault(404, 'Not Found', 404);
        } else {
            return new RestResponse(json_encode(['success' => true]), 200);
        }
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
