<?php

namespace Bloomkit\Core\Entities;

use Bloomkit\Core\Utilities\GuidUtils;
use Bloomkit\Core\Entities\Descriptor\EntityDescriptor;
use Bloomkit\Core\Entities\Exceptions\FieldNotFoundException;
use Bloomkit\Core\Entities\Fields\FieldType;

/**
 * An Entity represents a specific dataset based on the structure defined in the EntityDescriptor.
 */
class Entity
{
    /**
     * @var string
     */
    private $creationDate;

    /**
     * @var string
     */
    private $datasetId;

    /**
     * @var EntityDescriptor
     */
    private $descriptor;

    /**
     * @var array
     */
    private $fieldValues = [];

    /**
     * @var string
     */
    private $modificationDate;

    /**
     * Construktor.
     *
     * @param EntityDescriptor $entityDescriptor The Entity Descriptor this Entity is based on
     */
    public function __construct(EntityDescriptor $entityDescriptor)
    {
        $this->fieldValues = [];
        $this->descriptor = $entityDescriptor;
        $this->initializeFieldValues();
        if ($this->descriptor->getIdType() == EntityDescriptor::IDTYPE_UUID) {
            $this->datasetId = GuidUtils::generateGuid();
        }
    }

    /**
     * Magic setter for accessing values of Entity Fields.
     *
     * @param string $fieldId Id of the Field to get the value for
     *
     * @return mixed The Field value
     *
     * @throws FieldNotFoundException Throws if no field with the provided id is found
     */
    public function __get($fieldId)
    {
        if (array_key_exists($fieldId, $this->fieldValues)) {
            return $this->fieldValues[$fieldId];
        } else {
            throw new FieldNotFoundException(sprintf('Field "%s" not found in entity', $fieldId));
        }
    }

    /**
     * Magic setter for accessing values of Entity Fields.
     *
     * @param string $fieldId Id of the Field to set the value for
     * @param mixed  $value   The value to set
     *
     * @throws FieldNotFoundException Throws if no field with the provided id is found
     */
    public function __set($fieldId, $value)
    {
        if (array_key_exists($fieldId, $this->fieldValues)) {
            $fieldDesc = $this->descriptor->getField($fieldId);
            if ($fieldDesc->getFieldType() == FieldType::PDynFTReference) {
                $value = GuidUtils::decompressGuid($value);
            }
            $this->fieldValues[$fieldId] = $value;
        } else {
            throw new FieldNotFoundException(sprintf('Field "%s" not found in entity', $fieldId));
        }
    }

    /**
     * Check if a sepecific Field exists in this Entity.
     *
     * @param string $fieldId The id of the Field to check for
     *
     * @return bool True if Field exists, false if not
     */
    public function fieldExist($fieldId)
    {
        return array_key_exists($fieldId, $this->fieldValues);
    }

    /**
     * Returns the creation date of the Entity.
     *
     * @return \DateTime The creation date of the Entity
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Returns a hash for the Field values - primarily used for comparing two Entities.
     *
     * @return string A Hash of the Field values
     */
    public function getDatasetHash()
    {
        return md5($this->getDatasetJson());
    }

    /**
     * Returns the dataset id of the Entity.
     *
     * @return mixed The dataset id of the Entity
     */
    public function getDatasetId()
    {
        return $this->datasetId;
    }

    /**
     * Copies values from $fieldValues to the entity if possible
     */
    public function copyValuesFromArray(array $fieldValues): void
    {
        foreach ($fieldValues as $key => $value) {
            if ($this->fieldExist($key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Returns the Field values as a associative array.
     *
     * @param array Array of options
     *
     * @return array The field values of the entity
     */
    public function getDatasetAsArray(array $options = [])
    {
        $entityDesc = $this->getDescriptor();
        $fields = $entityDesc->getFields();

        $content = [];
        foreach ($fields as $field) {
            if ((!isset($options['includeAbstract'])) || (($options['includeAbstract']) !== true)) {
                if ($field->getIsAbstract()) {
                    continue;
                }
            }
            $fieldId = $field->getFieldId();
            $fieldValue = $this->$fieldId;
            if ($fieldValue === true) {
                $fieldValue = '1';
            } elseif ($fieldValue === false) {
                $fieldValue = '0';
            }
            $content[$fieldId] = (string) $fieldValue;
        }
        ksort($content);

        return $content;
    }

    /**
     * Returns the Field values as a json string.
     *
     * @return string The json encoded Field values of the Entity
     */
    public function getDatasetJson()
    {
        return json_encode($this->getDatasetAsArray());
    }

    /**
     * Returns the EntityDescriptor.
     *
     * @return EntityDescriptor The EntityDescriptor this Entity is based on
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * Returns the modification-date of the Entity.
     *
     * @return \DateTime The modification date of the Entity
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Initialize the fieldValues array with the default value for every field.
     */
    private function initializeFieldValues()
    {
        $fields = $this->descriptor->getFields();
        foreach ($fields as $field) {
            $fieldId = $field->getFieldId();
            if (isset($this->fieldValues[$fieldId]) == false) {
                $this->fieldValues[$fieldId] = $field->getDefaultValue();
            }
        }
    }

    /**
     * Sets the creation-date for the Entity.
     *
     * @param string|\DateTime $date The DateTime to set
     */
    public function setCreationDate($date)
    {
        if ($date instanceof \DateTime) {
            $this->creationDate = $date->format('Y-m-d H:i:s');
        } else {
            $this->creationDate = $date;
        }
    }

    /**
     * Sets the dataset id for the Entity.
     *
     * @param mixed $value The id to set
     */
    public function setDatasetId($value)
    {
        if ($this->descriptor->getIdType() == EntityDescriptor::IDTYPE_UUID) {
            $this->datasetId = GuidUtils::decompressGuid($value);
        } else {
            $this->datasetId = $value;
        }
    }

    /**
     * Sets the modification-date for the Entity.
     *
     * @param string|\DateTime $date The DateTime to set
     */
    public function setModificationDate($date)
    {
        if ($date instanceof \DateTime) {
            $this->modificationDate = $date->format('Y-m-d H:i:s');
        } else {
            $this->modificationDate = $date;
        }
    }
}
