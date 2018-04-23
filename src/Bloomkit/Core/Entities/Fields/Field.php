<?php

namespace Bloomkit\Core\Entities\Fields;

use Bloomkit\Core\Database;
use Bloomkit\Core\Database\DbDataType;

/**
 * Class for describing a dataset field.
 */
class Field
{
    /**
     * @var string
     */
    protected $columnName;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var bool
     */
    protected $duplicateCheck;

    /**
     * @var string
     */
    protected $fieldId;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $fieldType;

    /**
     * @var bool
     */
    protected $isAbstract;

    /**
     * @var bool
     */
    protected $isHidden;

    /**
     * @var bool
     */
    protected $isMandatory;

    /**
     * @var bool
     */
    protected $isSearchable;

    /**
     * @var bool
     */
    protected $isSystemField;

    /**
     * @var bool
     */
    protected $mailMerge;

    /**
     * @var string
     */
    protected $refEntityName;

    /**
     * @var string
     */
    protected $refFieldName;

    /**
     * Constructor.
     *
     * @param string      $fieldType
     * @param string      $fieldId
     * @param string|null $fieldName
     */
    public function __construct($fieldType, $fieldId, $fieldName = null)
    {
        $this->fieldId = $fieldId;
        if (is_null($fieldName)) {
            $this->fieldName = $fieldId;
        } else {
            $this->fieldName = $fieldName;
        }
        $this->columnName = $fieldId;
        $this->fieldType = $fieldType;
    }

    /**
     * Returns the column name.
     *
     * @return string The column name
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Returns the default value.
     *
     * @return mixed The default value
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Returns the database-type for this field.
     *
     * @return string Name of the (generic) database type
     */
    public function getFieldDbType()
    {
        switch ($this->fieldType) {
            case FieldType::PDynFTPercent:
                return DbDataType::Decimal;
            case FieldType::PDynFTCurrency:
                return DbDataType::Decimal;
            case FieldType::PDynFTMemo:
                return DbDataType::Text;
            case FieldType::PDynFTPassword:
                return DbDataType::Varchar;
            case FieldType::PDynFTReference:
                return DbDataType::UUID;
            case FieldType::PDynFTEnum:
                return DbDataType::Varchar;
            case FieldType::PDynFTBoolean:
                return DbDataType::Boolean;
            case FieldType::PDynFTInteger:
                return DbDataType::Integer;
            case FieldType::PDynFTDecimal:
                return DbDataType::Decimal;
            case FieldType::PDynFTString:
                return DbDataType::Varchar;
            case FieldType::PDynFTDate:
                return DbDataType::Date;
            case FieldType::PDynFTDuration:
                return DbDataType::Varchar;
            case FieldType::PDynFTTime:
                return DbDataType::Time;
            case FieldType::PDynFTTimestamp:
                return DbDataType::Timestamp;
        }
        throw new InvalidFieldTypeException(sprintf('Invalid FieldType: "%s"', $fieldType));
    }

    /**
     * Returns the field id.
     *
     * @return string The field id
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Returns the field name.
     *
     * @return string The field name
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Returns the field type.
     *
     * @return string The field type
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * Returns the isAbstract flag of the field.
     *
     * @return bool True if field is abstract, false if not
     */
    public function getIsAbstract()
    {
        return $this->isAbstract;
    }

    /**
     * Returns the isHidden flag of the field.
     *
     * @return bool True if field is a system-field, false if not
     */
    public function getIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * Returns the isMandatory flag of the field.
     *
     * @return bool True if field is mandatory, false if not
     */
    public function getIsMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * Returns the isSearchable flag of the field.
     *
     * @return bool True if field is searchable, false if not
     */
    public function getIsSearchable()
    {
        return $this->isSearchable;
    }

    /**
     * Returns the isSystem flag of the field.
     *
     * @return bool True if field is a system-field, false if not
     */
    public function getIsSystemField()
    {
        return $this->isSystemField;
    }

    /**
     * Returns the name of a referenced entity.
     *
     * @return string|null The name of a referenced entity - or null if not set
     */
    public function getRefEntityName()
    {
        return $this->refEntityName;
    }

    /**
     * Returns the name of a referenced field.
     *
     * @return string|null The name of a referenced field - or null if not set
     */
    public function getRefField()
    {
        return $this->refFieldName;
    }

    /**
     * Checks if field has a reference to another field.
     *
     * @return bool True if field has a configured reference, false if not
     */
    public function hasReference()
    {
        return isset($this->refEntityName) && ('' != $this->refEntityName) &&
        isset($this->refFieldName) && ('' !== $this->refFieldName);
    }

    /**
     * Sets the default value of the field.
     *
     * @param mixed $value The value to set
     */
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }

    /**
     * Sets the dublicate check of the field.
     *
     * @param bool $value The value to set
     */
    public function setDuplicateCheck($value)
    {
        $this->duplicateCheck = $value;
    }

    /**
     * Sets the field name.
     *
     * @param string $value The field-name to set
     */
    public function setFieldName($value)
    {
        $this->fieldName = $value;
    }

    /**
     * Sets the isAbstract flag.
     *
     * @param bool $value The value to set
     */
    public function setIsAbstract($value)
    {
        $this->isAbstract = $value;
    }

    /**
     * Sets the isHidden flag.
     *
     * @param bool $value The value to set
     */
    public function setIsHidden($value)
    {
        $this->isHidden = $value;
    }

    /**
     * Sets the isMandatory flag.
     *
     * @param bool $value The value to set
     */
    public function setIsMandatory($value)
    {
        $this->isMandatory = $value;
    }

    /**
     * Sets the isSearchable flag.
     *
     * @param bool $value The value to set
     */
    public function setIsSearchable($value)
    {
        $this->isSearchable = $value;
    }

    /**
     * Sets the isSystemField flag.
     *
     * @param bool $value The value to set
     */
    public function setIsSystemField($value)
    {
        $this->isSystemField = $value;
    }

    /**
     * Sets the mailMerge flag.
     *
     * @param bool $value The value to set
     */
    public function setMailMerge($value)
    {
        $this->mailMerge = $value;
    }

    /**
     * Set a field reference.
     *
     * @param string $refEntityName The referencing entity
     * @param string $refFieldName  The referencing field
     */
    public function setReference($refEntityName, $refFieldName)
    {
        $this->refEntityName = $refEntityName;
        $this->refFieldName = $refFieldName;
    }
}
