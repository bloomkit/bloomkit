<?php

namespace Bloomkit\Core\Rest;

use Bloomkit\Core\Module\Controller;
use Bloomkit\Core\Entities\EntityManager;
use Bloomkit\Core\Rest\Exceptions\RestFaultException;
use Bloomkit\Core\Entities\Services\CrudServiceInterface;
use Bloomkit\Core\Entities\Services\CrudService;
use Bloomkit\Core\Entities\Services\ListOutputParameters;
use Bloomkit\Core\Entities\Services\ListResult;

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
        $listResult = $this->service->getList($entityDescName, $filterStr, $listParams);

        return $this->createEntityListResponse($listResult);
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
        $result->determineTotalCount = true;

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

    protected function createEntityListResponse(ListResult $listResult)
    {
        $response = new RestResponse();
        $response->setEntityList($listResult, $listResult->getTotalCount());
        $response->setStatusCode(200);

        return $response;
    }

    protected function requireRequestData(): array
    {
        $request = $this->getRequest();
        $requestData = $request->getJsonData();

        if (is_null($requestData)) {
            throw new RestFaultException(400, 'Invalid request: No JSON found.');
        }

        return $requestData;
    }

    protected function requireRequestDataField(string $fieldName)
    {
        $requestData = $this->requireRequestData();
        if (!array_key_exists($fieldName, $requestData)) {
            throw new RestFaultException(400, "$fieldName not set", 400);
        }

        return $requestData[$fieldName];
    }

    protected function requireRequestDataFieldAsString(string $fieldName): string
    {
        return $this->requireRequestDataField($fieldName);
    }

    protected function requireRequestDataFieldAsArray(string $fieldName): array
    {
        return $this->requireRequestDataField($fieldName);
    }

    public function insert()
    {
        $requestData = $this->requireRequestData();
        $dsId = $this->service->insert($this->entityDescName, $requestData);

        $result['success'] = true;
        $result['id'] = $dsId;

        return new RestResponse(json_encode($result), 200);
    }

    public function updateByFilter($query)
    {
        $requestData = $this->requireRequestData();
        $result = $this->service->updateByFilter($this->entityDescName, $query, $requestData);
        if (!$result) {
            return RestResponse::createFault(404, 'Not Found', 404);
        } else {
            return new RestResponse(json_encode(['success' => true]), 200);
        }
    }

    public function updateById($dsId)
    {
        $requestData = $this->requireRequestData();
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
