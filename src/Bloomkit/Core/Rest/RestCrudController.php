<?php

namespace Bloomkit\Core\Rest;

use Bloomkit\Core\Module\Controller;
use Bloomkit\Core\Database\PbxQL\Filter;
use Bloomkit\Core\Entities\Entity;

class RestCrudController extends Controller
{
    /**
     * @var string
     */
    protected $entityDescName;

    public function deleteById($dsId)
    {
        $entityManager = $this->application->getEntityManager();
        $entityDesc = $entityManager->getEntityDescriptor($this->entityDescName);
        $entity = $entityManager->loadById($entityDesc, $dsId);

        if (false == $entity) {
            return RestResponse::createFault(404, 'Not Found', 404);
        }

        $entityManager->delete($entity);
        $result['success'] = true;

        return new RestResponse(json_encode($result), 200);
    }

    public function getDatasetByFilter($query)
    {
        $entityManager = $this->application->getEntityManager();

        $entityDesc = $entityManager->getEntityDescriptor($this->entityDescName);
        $filter = new Filter($entityDesc, $query, $entityDesc->getDatabaseConnection());
        $entities = $entityManager->loadList($entityDesc, $filter, 1);
        $entities = $entities->getItems();

        if (1 != count($entities)) {
            return RestResponse::createFault(404, 'Not Found', 404);
        }

        $response = new RestResponse();
        $response->setEntity(reset($entities));
        $response->setStatusCode(200);

        return $response;
    }

    public function getDatasetById($dsId)
    {
        $entityManager = $this->application->getEntityManager();
        $entityDesc = $entityManager->getEntityDescriptor($this->entityDescName);
        $entity = $entityManager->loadById($entityDesc, $dsId);
        if (false == $entity) {
            $response = RestResponse::createFault(404, sprintf('User "%s" not found', $dsId));

            return $response;
        }
        $response = new RestResponse();
        $response->setEntity($entity);
        $response->setStatusCode(200);

        return $response;
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

        $entityManager = $this->application->getEntityManager();
        $entityDesc = $entityManager->getEntityDescriptor($entityDescName);

        if (is_null($filter)) {
            $filterStr = $params->get('filter');
            if (isset($filterStr)) {
                $filter = new Filter($entityDesc, '* like "%'.$filterStr.'%"', $entityManager->getDatabaseConnection());
            }
        }

        $entitites = $entityManager->loadList($entityDesc, $filter, $limit, $offset, $orderBy, $orderAsc);
        $count = $entityManager->getCount($entityDesc, $filter);
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

        $entityManager = $this->application->getEntityManager();
        $entityDesc = $entityManager->getEntityDescriptor($this->entityDescName);
        $entity = new Entity($entityDesc);

        foreach ($requestData as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }

        $entityManager->save($entity);

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

        $entityManager = $this->application->getEntityManager();
        $entityDesc = $entityManager->getEntityDescriptor($this->entityDescName);
        $filter = new Filter($entityDesc, $query, $entityManager->getDatabaseConnection());
        $entities = $entityManager->loadList($entityDesc, $filter, 1);
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

        $entityManager->update($entity);
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

        $entityManager = $this->application->getEntityManager();
        $entityDesc = $entityManager->getEntityDescriptor($this->entityDescName);
        $entity = $entityManager->loadById($entityDesc, $dsId);

        if (false == $entity) {
            return RestResponse::createFault(404, 'Not Found', 404);
        }

        foreach ($requestData as $key => $value) {
            if ($entity->fieldExist($key)) {
                $entity->$key = $value;
            }
        }
        $entityManager->update($entity);
        $result['success'] = true;

        return new RestResponse(json_encode($result), 200);
    }
}
