<?php

namespace Bloomkit\Core\Database\PbxQl;

use Bloomkit\Core\Database\DbDataType;
use Bloomkit\Core\Entities\Descriptor\EntityDescriptor;
use Bloomkit\Core\Database\PbxQl\Exceptions\SearchQueryParsingException;
use Bloomkit\Core\Fields\Field;
use Doctrine\DBAL\Connection;

/**
 * Class for generating SQL queries from PbxQl statements.
 */
class Filter
{
    /**
     * @var Connection
     */
    private $dbCon;

    /**
     * @var string
     */
    private $lastPrefix;

    /**
     * @var EntityDescriptor
     */
    private $entityDesc;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $sql;

    /**
     * Constructor.
     *
     * @param EntityDescriptor $entityDesc Description for the entity to filter
     * @param string           $query      PbxQl Query to use
     * @param Connection       $dbCon      Database connection to use
     */
    public function __construct(EntityDescriptor $entityDesc, $query, Connection $dbCon)
    {
        $this->dbCon = $dbCon;
        $this->entityDesc = $entityDesc;
        $this->query = trim($query);
    }

    /**
     * Get SQL Query from filter.
     *
     * @param string $prefix Optional prefix to use for keys
     *
     * @return string The generated SQL query
     */
    public function getSql($prefix = '')
    {
        if (!isset($this->sql) || ($prefix != $this->lastPrefix)) {
            $this->parse($prefix);
        }

        return $this->sql;
    }

    /**
     * Build a SQL query(part) from a key, a value and an operator.
     *
     * @param string $key    The key of the database-field. Can be a wildcard (*) too
     * @param string $op     The comparing operator to use (=, !=, <, > etc)
     * @param string $value  The value to use
     * @param string $prefix Optional prefix to use for keys
     *
     * @return string The generated SQL query(part)
     */
    private function getSqlFilterPart($key, $op, $value, $prefix = '')
    {
        //define allowed operators
        $operators = ['LIKE', '=', '!=', '>=', '>', '<=', '<', 'ISNULL', 'ISNOTNULL'];

        //clean values
        $key = trim(str_replace(['"', "'", '´', '`'], '', $key));
        $op = strtoupper(trim($op));

        if (isset($value)) {
            $value = str_replace(['"', "'"], '', $value);
        }

        //check if operator is valid
        if (array_search($op, $operators) === false) {
            throw new SearchQueryParsingException('Unknown operator: '.$op);
        }
        //if wildcard key is set, use all fields marked as searchable
        if ($key == '*') {
            $sql = '';
            $fieldList = $this->entityDesc->getFields();
            $autoSearchFields = [];
            foreach ($fieldList as $field) {
                if ($field->getIsSearchable()) {
                    $autoSearchFields[] = $field;
                }
            }

            foreach ($autoSearchFields as $asField) {
                if ($sql != '') {
                    $sql .= ' or ';
                }
                $sql .= $this->getSqlFilterPart($asField->getColumnName(), $op, $value, $prefix);
            }

            if ($sql != '') {
                $sql = '('.$sql.')';
            }

            return $sql;
        }

        //find the field with the provides key
        $field = $this->entityDesc->getField($key);
        if (!$field) {
            throw new SearchQueryParsingException('Field not found: '.$key.' in '.$this->entityDesc->getEntityName());
        }

        //if ($this->isSystemField($key))
        //$key = 'ms."'.$key.'"';
        //else if ($this->isCustomField($key))
        //$key = 'mc."'.$key.'"';
        $key = $this->dbCon->quoteColumnName($key);
        if ('' != $prefix) {
            $key = $prefix.'.'.$key;
        }

        $dbFieldType = $field->getFieldDbType();

        if ($dbFieldType == DbDataType::Boolean) {
            // Check for valid operators
            if (('=' != $op) && ('!=' != $op)) {
                throw new SearchQueryParsingException('Invalid operator for boolean field: '.$op);
            }

            //ignore value if operator is ISNULL or ISNOTNULL
            if ('ISNULL' == $op) {
                return $key.' is null';
            } elseif ('ISNOTNULL' == $op) {
                return $key.' is not null';
            }

            //format value
            if ((true === $value) || ('1' === $value) || ('true' === strtolower($value))) {
                $value = 'true';
            } else {
                $value = 'false';
            }

            return $key.' '.$op.' '.$value;
        } elseif ((DbDataType::Integer == $dbFieldType) || (DbDataType::Decimal == $dbFieldType)) {
            // Check for valid operators
            if (('=' != $op) && ('!=' != $op) && ('<' != $op) && ('>' != $op) && ('>=' != $op) && ('<=' != $op) && ('ISNULL' != $op) && ('ISNOTNULL' != $op)) {
                throw new SearchQueryParsingException('Invalid operator for numeric field: '.$op);
            }
            //ignore value if operator is ISNULL or ISNOTNULL
            if ('ISNULL' == $op) {
                return $key.' is null';
            } elseif ('ISNOTNULL' == $op) {
                return $key.' is not null';
            }
            //format value
            if (is_numeric($value)) {
                // $value = $this->dbCon->getConnection()->quote($value, PDO::PARAM_INT);
                // $value = $this->dbCon->getConnection()->quote($value, PDO::PARAM_INT);
            } else {
                $value = '0';
            }

            return $key.' '.$op.' '.$value;
        } elseif ((DbDataType::Date == $dbFieldType) || (DbDataType::Time == $dbFieldType) || (DbDataType::Timestamp == $dbFieldType)) {
            // Check for valid operators
            if (('=' != $op) && ('!=' != $op) && ('<' != $op) && ('>' != $op) && ('>=' != $op) && ('<=' != $op) && ('ISNULL' != $op) && ('ISNOTNULL' != $op)) {
                throw new SearchQueryParsingException('Invalid operator for date field: '.$op);
            }
            //ignore value if operator is ISNULL or ISNOTNULL
            if ('ISNULL' == $op) {
                if (('PDynFTDate' != $dbFieldType) && ('PDynFTTime' != $dbFieldType) && ('PDynFTTimestamp' != $dbFieldType)) {
                    return '('.$key.' is null or '.$key."= '')";
                } else {
                    return $key.' is null';
                }
            } elseif ('ISNOTNULL' == $op) {
                if (('PDynFTDate' != $dbFieldType) && ('PDynFTTime' != $dbFieldType) && ('PDynFTTimestamp' != $dbFieldType)) {
                    return '('.$key.' is not null and '.$key."<> '')";
                } else {
                    return $key.' is not null';
                }
            }

            //format value
            //$value = $this->dbCon->getConnection()->quote($value, PDO::PARAM_STR);
            $value = '\''.$value.'\'';

            return $key.' '.$op.' '.$value;
        } elseif ((DbDataType::Varchar == $dbFieldType) || (DbDataType::Text == $dbFieldType) || (DbDataType::Char == $dbFieldType) || (DbDataType::UUID == $dbFieldType)) {
            // Check for valid operators
            if (('LIKE' != $op) && ('=' != $op) && ('!=' != $op) && ('ISNULL' != $op) && ('ISNOTNULL' != $op)) {
                throw new SearchQueryParsingException('Invalid operator for text field: '.$op);
            }
            //ignore value if operator is ISNULL or ISNOTNULL
            if ('ISNULL' == $op) {
                if ('uuid' != $dbFieldType) {
                    return '('.$key.' is null or '.$key."= '')";
                } else {
                    return $key.' is null';
                }
            } elseif ('ISNOTNULL' == $op) {
                if ('uuid' != $dbFieldType) {
                    return '('.$key.' is not null and '.$key."<> '')";
                } else {
                    return $key.' is not null';
                }
            }

            //format value
            // $value = $this->dbCon->getConnection()->quote($value, PDO::PARAM_STR);
            $value = '\''.$value.'\'';
            if ('LIKE' == $op) {
                return 'lower('.$key.') like lower('.$value.')';
            } else {
                return $key.' '.$op.' '.$value;
            }
        } else {
            throw new SearchQueryParsingException('Unknown dbType: '.$field['dbType']);
        }
    }

    /**
     * Get SQL for a PbxQL expression-list.
     *
     * @param array  $exprList Array of expressions to use (from PbxQL parser)
     * @param string $prefix   Optional prefix to use for keys
     *
     * @return string The SQL query for the expression-list
     */
    private function getSqlForExprList($exprList, $prefix = '')
    {
        $key = false;
        $cmpOp = false;
        $bindOp = false;
        $value = false;
        $sql = '';
        $expectNext = 'colRef';

        foreach ($exprList as $expr) {
            if ('colRef' == $expr['exprType']) {
                if ('colRef' != $expectNext) {
                    throw new SearchQueryParsingException($expr['exprType'].' found, '.$expectNext.' expected.');
                }
                $key = $expr['expression'];
                $expectNext = 'cmpOp';
            } elseif ('operator' == $expr['exprType']) {
                if ('cmpOp' == $expectNext) {
                    $cmpOp = $expr['expression'];
                    if (('ISNULL' == $cmpOp) || ('ISNOTNULL' == $cmpOp)) {
                        $expectNext = 'bindOp';
                    } else {
                        $expectNext = 'value';
                    }
                } elseif ('bindOp' == $expectNext) {
                    $bindOp = $expr['expression'];
                    $expectNext = 'colRef';
                } else {
                    throw new SearchQueryParsingException($expr['exprType'].' found, '.$expectNext.' expected.');
                }
            } elseif ('value' == $expr['exprType']) {
                if ('value' != $expectNext) {
                    throw new SearchQueryParsingException($expr['exprType'].' found, '.$expectNext.' expected.');
                }
                $value = $expr['expression'];
                $expectNext = 'bindOp';
            } elseif ('expression' == $expr['exprType']) {
                if ('colRef' != $expectNext) {
                    throw new SearchQueryParsingException($expr['exprType'].' found, '.$expectNext.' expected.');
                }
                if (false != $bindOp) {
                    $sql .= $bindOp.' ';
                }
                $sql .= '('.$this->getSqlForExprList($expr['subExpr'], $prefix).') ';
                $key = false;
                $cmpOp = false;
                $bindOp = false;
                $value = false;
                $expectNext = 'bindOp';
            }
            if ((false != $key) && (false != $cmpOp) && ((false != $value) || ('ISNULL' == $cmpOp) || ('ISNOTNULL' == $cmpOp))) {
                if (false != $bindOp) {
                    $sql .= $bindOp.' ';
                }

                if (('"id"' == $key) || ('id' == $key)) {
                    $sql .= 'id '.$cmpOp.' '.$value;
                } else {
                    $sql .= $this->getSqlFilterPart($key, $cmpOp, $value, $prefix).' ';
                }
                $key = false;
                $cmpOp = false;
                $bindOp = false;
                $value = false;
            }
        }

        return $sql;
    }

    /**
     * Parse.
     *
     * @param string $prefix Prefix
     */
    public function parse($prefix = '')
    {
        if ($this->query == '') {
            $this->sql = '';

            return;
        }

        $parser = new Parser();
        $expressions = $parser->parse($this->query);
        $this->sql = $this->getSqlForExprList($expressions, $prefix);
    }
}
