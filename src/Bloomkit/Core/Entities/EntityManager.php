<?php

namespace Bloomkit\Core\Entities;

use Bloomkit\Core\Database\DbMaster;
use Bloomkit\Core\Utilities\Collection;
use Bloomkit\Core\Database\PbxQL\Filter;
use Bloomkit\Core\Entities\Descriptor\EntityDescriptor;

/**
 * Provides CRUD functions for Entities.
 */
class EntityManager
{
    /**
     * @var array
     */
    private $entityDescriptions = [];

    /**
     * @var DbMaster
     */
    private $dbCon;
    
    /**
     * Construktor.
     *
     * @param DbMaster $dbCon Database connection to use
     */
    public function __construct(DbMaster $dbCon)
    {
        $this->dbCon = $dbCon;
    }    

    /**
     * Deletes an given Entity from database
     *
     * @param Entity $entity Entity to delete
     */
    public function delete(Entity $entity)
    {
        $dsId = $entity->getDatasetId();
        $entityDesc = $entity->getDescriptor();
    
        $sql = 'DELETE FROM '.$this->dbCon->quoteTableName($entityDesc->getTableName());
        $sql .= ' where '.$this->dbCon->quoteColumnName('id').'= ?;';
    
        $stmt = $this->dbCon->prepare($sql);
        if ($entityDesc->getIdType() == EntityDescriptor::IDTYPE_UUID) {
            $stmt->bindValue(1, $dsId, \PDO::PARAM_STR);
        } else {
            $stmt->bindValue(1, $dsId, \PDO::PARAM_INT);
        }
    
        $stmt->execute();
    }

    /**
     * Returns the rowCount for a given EntityDescriptor and an optional Filter
     *
     * @param EntityDescriptor $entityDesc EntityDescriptor object for the request
     * @param Filter|null $filter The Filter to use for the request (if any)
     *  
     */
    public function getCount(EntityDescriptor $entityDesc, Filter $filter = null)
    {
        $sql = 'select count(*) from '.$entityDesc->getTableName().' as bt ';
    
        $fields = $entityDesc->getFields();
    
        foreach ($fields as $field) {
            if ($field->hasReference()) {
                $entityDesc = $this->getEntityDescriptor($field->getRefEntityName());
                if (is_null($entityDesc)) {
                    continue;
                }
                $refField = $field->getRefField();
                if ($entityDesc->hasField($refField) == false) {
                    continue;
                }
                $tblName = $entityDesc->getTableName();
                $reference['entityDesc'] = $entityDesc;
                $reference['refField'] = $refField;
    
                $sql .= 'left join '.$tblName.' on bt.'.$field->getFieldId().' = '.$tblName.'.'.$refField.' ';
            }
        }
    
        $where = '';
        $whereCnt = 0;
        if ($entityDesc->getRecoveryMode()) {
            ++$whereCnt;
            if (array_search('is_deleted', $criteria)) {
                $showDeleted = $criteria['is_deleted'] === true;
                if ($showDeleted) {
                    $where .= 'is_deleted = TRUE ';
                } else {
                    $where .= 'is_deleted = FALSE ';
                }
            }
        }
    
        if (isset($filter)) {
            $filterSql = $filter->getSql('bt');
            if (trim($filterSql) != '') {
                if ($whereCnt > 0) {
                    $where .= 'and ';
                }
                $where .= '('.$filterSql.')';
                ++$whereCnt;
            }
        }
    
        if ($whereCnt > 0) {
            $sql .= 'where '.$where;
        }
    
        try {
            $stmt = $this->dbCon->getConnection()->query($sql);
            $rowCount = (int) $stmt->fetchColumn(0);
    
            return $rowCount;
        } catch (PDOException $e) {
            throw new EDbError($e->getMessage());
        }
    }


    /**
     * Returns the database connection
     *
     * @return DbMaster The database connection 
     */
    public function getDatabaseConnection()
    {
        return $this->dbCon;
    }
    
    /**
     * Create (if not already done) and returns an EntityDescriptor object based on the class name
     *
     * @param string $className The Name of the EntityDescriptor class
     *
     * @return EntityDescriptor The requested object
     */
    public function getEntityDescriptor($className)
    {
        if (isset($this->entityDescriptions[$className])) {
            return $this->entityDescriptions[$className];
        }

        if ((class_exists($className)) && (is_subclass_of($className, 'Bloomkit\Core\Entities\Descriptor\EntityDescriptor'))) {
            $entityDesc = new $className();
            $this->entityDescriptions[$className] = $entityDesc;

            return $entityDesc;
        }
    }
    
    /**
     * Returns a SQL insert statement and the bind-values for for a given Entity
     *
     * @param Entity $entity The Entity to generate an insert statement for
     * @param array $replacements An array to save the bind-values for the statement to
     *
     * @return string The generated SQL statement
     */
    private function getInsertSql(Entity $entity, &$replacements)
    {
        $entityDesc = $entity->getDescriptor();
        $fields = $entityDesc->getFields();
        $dsId = $entity->getDatasetId();
    
        $replacements = array();
        $columns = array();
        $values = array();
    
        if ($entityDesc->getIdType() == EntityDescriptor::IDTYPE_UUID) {
            $columns[] = $this->dbCon->quoteColumnName('id');
            $values[] = $this->dbCon->quoteValue($dsId, DbDataType::UUID);
        }
    
        if ($entityDesc->getLogCreationDate()) {
            $columns[] = $this->dbCon->quoteColumnName(EntityDescriptor::ColNameCreationStamp);
            if (is_null($entity->getCreationDate())) {
                $values[] = 'now()';
            } else {
                $values[] = $this->dbCon->quoteValue($entity->getCreationDate(), DbDataType::Varchar);
            }
        }
    
        if ($entityDesc->getLogModificationDate()) {
            $columns[] = $this->dbCon->quoteColumnName(EntityDescriptor::ColNameModificationStamp);
            if (is_null($entity->getModificationDate())) {
                $values[] = 'now()';
            } else {
                $values[] = $this->dbCon->quoteValue($entity->getModificationDate(), DbDataType::Varchar);
            }
        }
    
        foreach ($fields as $field) {
            if ($field->getIsAbstract() == true) {
                continue;
            }
            $fieldId = $field->getFieldId();
            $fieldValue = $entity->$fieldId;
            $fieldDbType = $field->getFieldDbType();
            $columns[] = $this->dbCon->quoteColumnName($fieldId);
            $replacement['value'] = $fieldValue;
            $replacement['type'] = $this->dbCon->getPDOType($fieldValue, $fieldDbType);
            $replacements[] = $replacement;
            $values[] = '?';
        }
    
        $sql = 'INSERT INTO '.$this->dbCon->quoteTableName($entityDesc->getTableName()).' ';
        $sql .= '('.implode(', ', $columns).') ';
        $sql .= 'VALUES ('.implode(', ', $values).')';
    
        return $sql;
    }
    
    /**
     * Returns a SQL update statement and the bind-values for for a given Entity
     *
     * @param Entity $entity The Entity to generate an update statement for
     * @param array $replacements An array to save the bind-values for the statement to 
     *
     * @return string The generated SQL statement
     */
    private function getUpdateSql(Entity $entity, &$replacements)
    {
        $entityDesc = $entity->getDescriptor();
        $fields = $entityDesc->getFields();
        $dsId = $entity->getDatasetId();
    
        $items = [];
        $replacements = [];
    
        if ($entityDesc->getLogModificationDate()) {
            $items[$this->dbCon->quoteColumnName(EntityDescriptor::ColNameModificationStamp)] = 'now()';
        }
    
        foreach ($fields as $field) {
            if ($field->getIsAbstract() == true) {
                continue;
            }
            $fieldId = $field->getFieldId();
            $fieldValue = $entity->$fieldId;
            $fieldDbType = $field->getFieldDbType();
            $items[$this->dbCon->quoteColumnName($fieldId)] = '?';
            $replacement['value'] = $fieldValue;
            $replacement['type'] = $this->dbCon->getPDOType($fieldValue, $fieldDbType);
            $replacements[] = $replacement;
        }
    
        $list = [];
        foreach ($items as $key => $value) {
            $list[] = $key.'='.$value;
        }
        $sql = 'UPDATE '.$this->dbCon->quoteTableName($entityDesc->getTableName()).' SET ';
        $sql .= implode(', ', $list);
        $sql .= ' where '.$this->dbCon->quoteColumnName('id').'= ?;';
    
        if ($entityDesc->getIdType() == EntityDescriptor::IDTYPE_UUID) {
            $replacement['value'] = $dsId;
            $replacement['type'] = \PDO::PARAM_STR;
        } else {
            $replacement['value'] = $dsId;
            $replacement['type'] = \PDO::PARAM_INT;
        }
        $replacements[] = $replacement;
    
        return $sql;
    }

    /**
     * Saves a new Entity to the database 
     *
     * @param Entity $entity The Entity to save
     *
     * @return string The id of the saved Entity
     */
    public function insert(Entity $entity)
    {
        $replacements = [];
        $entityDesc = $entity->getDescriptor();
        $sql = $this->getInsertSql($entity, $replacements);
    
        $stmt = $this->dbCon->prepare($sql);
    
        $paramIndex = 1;
        foreach ($replacements as $rep) {
            $stmt->bindValue($paramIndex++, $rep['value'], $rep['type']);
        }
    
        $stmt->execute();
        if ($entityDesc->getIdType() == EntityDescriptor::IDTYPE_SERIAL) {
            $dsId = $this->dbCon->getLastInsertId($entityDesc->getTableName(), 'id');
            $entity->setDatasetId($dsId);
        } else {
            $dsId = $entity->getDatasetId();
        }
    
        return $dsId;
    }    
    
    /**
     * Load a specific Entitiy by a filter (returns the first one, if multiple matches)
     *
     * @param EntityDescriptor  $entityDesc The descriptor for the Entity to load
     * @param Filter|null  $filter A filter to use for the request
     *
     * @return Entity|false The first matching Entity or false if not found
     */
    public function load(EntityDescriptor $entityDesc, Filter $filter = null)
    {
        $result = $this->loadList($entityDesc, $filter, 1);
        $result = $result->getAllItems();
        if (count($result) == 0) {
            return false;
        } else {
            return $result[0];
        }
    }
    
    /**
     * Load a specific Entitiy by its id
     *
     * @param EntityDescriptor  $entityDesc The descriptor for the Entity to load
     * @param string $id The id of the Entity to load
     *
     * @return Entity|false The matching Entity or false if not found 
     */
    public function loadById(EntityDescriptor $entityDesc, $id)
    {
        if ($entityDesc->getIdType() == EntityDescriptor::IDTYPE_UUID) {
            $value = '\''.GUIDUtils::decompressGUID($id).'\'';
        } else {
            $value = $id;
        }
        $filter = new Filter($entityDesc, '"id"='.$value, $this->dbCon);
    
        return $this->load($entityDesc, $filter);
    }

    /**
     * Load a list of Entities for a given EntityDescriptor
     *
     * @param EntityDescriptor  $entityDesc The descriptor for the Entities to load
     * @param Filter|null  $filter A filter to use for the request
     * @param int $limit The amount of Entities to load 
     * @param int $offset The offset to start loading Entities from
     * @param string|null $orderBy The id of the Field to order by
     * @param boolean $orderAsc Order ascending if true, descending if false 
     *
     * @return Collection A Collection containing the loaded Entities
     */
    public function loadList(EntityDescriptor $entityDesc, Filter $filter = null, $limit = 10, $offset = 0, $orderBy = null, $orderAsc = true)
    {
        $fields = $entityDesc->getFields();
        $result = new Collection();
        $tblCnt = 1;
    
        $sql = 'select bt.id ';
    
        if ($entityDesc->getLogCreationDate()) {
            $sql .= ',bt.creation_date ';
        }
    
        if ($entityDesc->getLogModificationDate()) {
            $sql .= ',bt.modification_date ';
        }
    
        $fieldSql = '';
        $joinSql = '';
    
        foreach ($fields as $field) {
            if ($field->getIsAbstract()) {
                continue;
            }
            $fieldSql .= ',';
            $fieldSql .= 'bt.'.$this->dbCon->quoteColumnName($field->getFieldId());
        }
    
        foreach ($fields as $field) {
            if ($field->hasReference()) {
                $refEntityDesc = $this->getEntityDescriptor($field->getRefEntityName());
                if (is_null($refEntityDesc)) {
                    continue;
                }
    
                $refField = $field->getRefField();
                if ($refEntityDesc->hasField($refField) == false) {
                    continue;
                }
    
                $alias = 'jt'.$tblCnt;
                ++$tblCnt;
    
                $refFields = $refEntityDesc->getFields();
                foreach ($refFields as $tmpField) {
                    if ($tmpField->getIsAbstract()) {
                        continue;
                    }
                    $fieldSql .= ',';
                    $fieldSql .= $alias.'.'.$this->dbCon->quoteColumnName($tmpField->getFieldId());
                }
    
                $tblName = $refEntityDesc->getTableName();
                $reference['entityDesc'] = $refEntityDesc;
                $reference['refField'] = $refField;
    
                $joinSql .= 'left join '.$tblName.' as '.$alias.' on bt.'.$field->getFieldId().' = '.$alias.'.'.$refField.' ';
            }
        }
    
        $sql .= $fieldSql.' ';
        $sql .= 'from '.$entityDesc->getTableName().' as bt ';
        $sql .= $joinSql.' ';
    
        $where = '';
        $whereCnt = 0;
        if ($entityDesc->getRecoveryMode()) {
            ++$whereCnt;
            if (array_search('bt.is_deleted', $criteria)) {
                $showDeleted = $criteria['is_deleted'] === true;
                if ($showDeleted) {
                    $where .= 'bt.is_deleted = TRUE ';
                } else {
                    $where .= 'bt.is_deleted = FALSE ';
                }
            }
        }
    
        if (isset($filter)) {
            $filterSql = $filter->getSql('bt');
            if (trim($filterSql) != '') {
                if ($whereCnt > 0) {
                    $where .= 'and ';
                }
                $where .= '('.$filterSql.')';
                ++$whereCnt;
            }
        }
    
        if ($whereCnt > 0) {
            $sql .= 'where '.$where;
        }
       
        if (isset($orderBy) && (($entityDesc->hasField($orderBy))) || ($orderBy == 'id')) {
            $sql .= ' order by bt.'.$this->dbCon->quoteColumnName($orderBy);
            if ($orderAsc) {
                $sql .= ' ASC ';
            } else {
                $sql .= ' DESC ';
            }
        } else {
            if ($entityDesc->getLogCreationDate()) {
                $sql .= ' order by bt.creation_date DESC ';
            }
        }
    
        if ((is_int($limit)) && ($limit > 0)) {
            $sql .= ' limit '.$limit;
        }
        if ((is_int($offset)) && ($offset > 0)) {
            $sql .= ' offset '.$offset;
        }
    
        try {
            $stmt = $this->dbCon->getConnection()->query($sql);
        } catch (PDOException $e) {
            throw new EDbError($e->getMessage());
        }
        foreach ($stmt as $row) {
            $entity = new Entity($entityDesc);
            if (isset($row['id'])) {
                $entity->setDatasetId($row['id']);
            }
    
            if ($entityDesc->getLogCreationDate()) {
                $entity->setCreationDate($row['creation_date']);
            }
    
            if ($entityDesc->getLogModificationDate()) {
                $entity->setModificationDate($row['modification_date']);
            }
    
            $fields = $entityDesc->getFields();
    
            foreach ($fields as $field) {
                $fieldCol = $field->getColumnName();

                if (array_key_exists($fieldCol, $row)) {
                    $entity->$fieldCol = $row[$fieldCol];
                }
            }
            $result->add($entity->getDatasetId(), $entity);
        }
    
        return $result;
    }

    /**
     * Updating a given entity in the database
     *
     * @param Entity $entity The Entity to update
     */
    public function update(Entity $entity)
    {
        $replacements = array();
        $entityDesc = $entity->getDescriptor();
        $sql = $this->getUpdateSql($entity, $replacements);
    
        $stmt = $this->dbCon->prepare($sql);
    
        $paramIndex = 1;
        foreach ($replacements as $rep) {
            $stmt->bindValue($paramIndex++, $rep['value'], $rep['type']);
        }
    
        $stmt->execute();
    }

    /**
     * Sets the modification timestamp for a given Entity to now
     *
     * @param Entity $entity The Entity to update the modification date
     */
    public function updateModificationDate(Entity $entity)
    {
        $entityDesc = $entity->getDescriptor();
    
        $dsId = $entity->getDatasetId();
    
        $sql = 'UPDATE '.$this->dbCon->quoteTableName($entityDesc->getTableName()).' SET ';
        $sql .= $this->dbCon->quoteColumnName(EntityDescriptor::ColNameModificationStamp).'= now()';
        $sql .= ' where '.$this->dbCon->quoteColumnName('id').'= ?;';
    
        $stmt = $this->dbCon->prepare($sql);
        if ($entityDesc->getIdType() == EntityDescriptor::IDTYPE_UUID) {
            $stmt->bindValue(1, $dsId, \PDO::PARAM_STR);
        } else {
            $stmt->bindValue(1, $dsId, \PDO::PARAM_INT);
        }
    
        $stmt->execute();
    }
}
