<?php

namespace Bloomkit\Core\Entities\Descriptor;

use Bloomkit\Core\Entities\Fields\Field;

/**
 * Class for describing an Entity.
 */
class EntityDescriptor
{
    const COL_NAME_CREATION_STAMP = 'creation_date';
    
    const COL_NAME_MODIFICATION_STAMP = 'modification_date';
    
    const IDTYPE_SERIAL = 'serial';

    const IDTYPE_UUID = 'uuid';
    
    /**
     * @var bool
     */
    protected $creationDateLogging;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string
     */
    protected $idType;

    /**
     * @var bool
     */
    protected $modificationDateLogging;

    /**
     * @var bool
     */
    protected $recoveryMode;

    /**
     * @var string
     */
    protected $tableName;

    public function __construct($entityName, $idType = self::IDTYPE_SERIAL)
    {
        $this->entityName = $entityName;
        $this->tableName = strtolower($entityName);
        $this->idType = $idType;
        $this->recoveryMode = false;
        $this->creationDateLogging = true;
        $this->modificationDateLogging = true;
    }

    /**
     * Add a new field to the entity.
     *
     * @param Field $field The field to add
     */
    public function addField(Field $field)
    {
        $fieldId = $field->getFieldId();
        unset($this->fields[$fieldId]);
        $this->fields[$fieldId] = $field;
    }

    /**
     * Returns the creationDateLogging mode.
     *
     * @return bool True if creationDateLogging is enabled, false if not
     */
    public function getCreationDateLogging()
    {
        return $this->creationDateLogging;
    }

    /**
     * Returns the name of the entity.
     *
     * @result string The name of the entity
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Returns a field by its id (or null if not found).
     *
     * @param string $fieldId The id of the field to find
     *
     * @return Field|null The field or null if not found
     */
    public function getField($fieldId)
    {
        if (isset($this->fields[$fieldId])) {
            return $this->fields[$fieldId];
        }
    }

    /**
     * Returns the number of field on this entity.
     *
     * @return int The number of fields
     */
    public function getFieldCount()
    {
        return count($this->fields);
    }

    /**
     * Return all fields definied for this entity.
     *
     * @return Fields[] Return an array of fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns the id-type of the entity (serial, uuid).
     *
     * @result string Id-type of the entity
     */
    public function getIdType()
    {
        return $this->idType;
    }

    /**
     * Returns the modificationDateLogging mode.
     *
     * @return bool True if modificationDateLogging is enabled, false if not
     */
    public function getModificationDateLogging()
    {
        return $this->modificationDateLogging;
    }

    /**
     * Returns the state of the recovery-mode.
     *
     * @result boolean The state of the recovery-mode
     */
    public function getRecoveryMode()
    {
        return $this->recoveryMode;
    }

    /**
     * Returns the name of the table for this entity.
     *
     * @result string The name of the table
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Check if entity has a specific field.
     *
     * @param string $fieldId Name of the field
     *
     * @result boolean True if entity has this field, false if not
     */
    public function hasField($fieldId)
    {
        foreach ($this->fields as $field) {
            if ($field->getFieldId() == $fieldId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes a field by its id.
     *
     * @param string $fieldId The id of the field to remove
     */
    public function removeField($fieldId)
    {
        unset($this->fields[$fieldId]);
    }

    /**
     * Sets the creationDateLogging mode.
     *
     * @param bool $mode True to enable creationDateLogging
     */
    public function setCreationDateLogging($mode)
    {
        $this->creationDateLogging = $mode;
    }

    /**
     * Sets the modificationDateLogging mode.
     *
     * @param bool $mode True to enable modificationDateLogging
     */
    public function setModificationDateLogging($mode)
    {
        $this->modificationDateLogging = $mode;
    }

    /**
     * Sets the recovery-mode. Used to decide if datasets can be deleted or have to be marked as deleted.
     *
     * @param bool $value True if datasets of this kind should be able to be recovered, else if not
     */
    public function setRecoveryMode($value)
    {
        $this->recoveryMode = $value;
    }

    /**
     * Sets the table name (used for persistance).
     *
     * @param string $tableName The name of the (db)-table to persist entities to (in lowercase)
     */
    public function setTableName($tableName)
    {
        $this->tableName = strtolower($tableName);
    }
}
