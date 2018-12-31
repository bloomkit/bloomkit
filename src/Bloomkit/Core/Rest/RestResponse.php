<?php

namespace Bloomkit\Core\Rest;

use Bloomkit\Core\Entities\Entity;
use Bloomkit\Core\Http\HttpResponse;
use Bloomkit\Core\Utilities\Repository;
use Bloomkit\Core\Entities\Descriptor\EntityDescriptor;
use Bloomkit\Core\Utilities\GuidUtils;
use Bloomkit\Core\Entities\Fields\FieldType;

/**
 * Representation of a REST response.
 */
class RestResponse extends HttpResponse
{
    /**
     * Constructor.
     *
     * {@inheritdoc}
     */
    public function __construct($content = '', $statusCode = 200, $headers = [], $cookies = [])
    {
        $headers['Content-type'] = 'application/json; charset=utf-8';
        parent::__construct($content, $statusCode, $headers, $cookies);
    }

    /**
     * Create a REST response with an error.
     *
     * @param int    $statusCode The HTTP status-code to return
     * @param string $message    The REST error message
     * @param int    $faultCode  The REST error code
     *
     * @return RestResponse The created response
     */
    public static function createFault($statusCode, $message, $faultCode = 0)
    {
        $error = array();
        $error['error'] = array();
        $error['error']['message'] = $message;
        $error['error']['faultCode'] = $faultCode;

        return new RestResponse(json_encode($error), $statusCode);
    }

    /**
     * Create a REST response.
     *
     * @param int   $statusCode The HTTP status-code to return
     * @param array $data       The data to return
     *
     * @return RestResponse The created response
     */
    public static function createResponse($statusCode, array $data)
    {
        return new RestResponse(json_encode($data), $statusCode);
    }

    /**
     * Set the content of the result with on Entity.
     *
     * @param Entity $entity The Entity to set (from EntityManager)
     */
    public function setEntity(Entity $entity)
    {
        $entityDesc = $entity->getDescriptor();
        $datasetItem = [];
        $fields = $entityDesc->getFields();
        foreach ($fields as $field) {
            $fieldId = $field->getFieldId();
            if ($field->getFieldType() == FieldType::PDynFTPassword) {
                $datasetItem[$fieldId] = '';
            } else {
                $datasetItem[$fieldId] = $entity->$fieldId;
            }
        }
        if ($entityDesc->getIdType() == EntityDescriptor::IDTYPE_UUID) {
            $datasetItem['id'] = GuidUtils::decompressGuid($entity->getDatasetId());
        } else {
            $datasetItem['id'] = $entity->getDatasetId();
        }
        $result = json_encode($datasetItem);
        $this->setContent($result);
    }

    /**
     * Set the content of the result with a list of entites.
     *
     * @param Repository $entities The entities to set (from EntityManager)
     * @param int|null   $count    if not set, count is the number of entities in the list
     */
    public function setEntityList(Repository $entities, $count = null)
    {
        if (is_null($count)) {
            $result['count'] = count($entities);
        } else {
            $result['count'] = $count;
        }
        foreach ($entities as $entity) {
            $entityDesc = $entity->getDescriptor();
            $datasetItem = [];
            $fields = $entityDesc->getFields();
            foreach ($fields as $field) {
                $fieldId = $field->getFieldId();
                if ($field->getFieldType() == FieldType::PDynFTPassword) {
                    $datasetItem[$fieldId] = '';
                } else {
                    $datasetItem[$fieldId] = $entity->$fieldId;
                }
            }
            if ($entityDesc->getIdType() == EntityDescriptor::IDTYPE_UUID) {
                $datasetItem['id'] = GuidUtils::decompressGuid($entity->getDatasetId());
            } else {
                $datasetItem['id'] = $entity->getDatasetId();
            }
            $result['list'][] = $datasetItem;
        }
        $result = json_encode($result);
        $this->setContent($result);
    }
}
