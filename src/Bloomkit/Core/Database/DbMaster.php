<?php

namespace Bloomkit\Core\Database;

use Doctrine\DBAL\DriverManager;
use Bloomkit\Core\Database\Exceptions\DbException;
use Bloomkit\Core\Database\Exceptions\DbConnectionException;

final class DbMaster
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $dbCon;

    /**
     * Constructor.
     *
     * @param string $dbName   Name of the database
     * @param string $dbUser   Username
     * @param string $dbPass   Password
     * @param string $dbHost   Hostname of the database server
     * @param int    $dbPort   Port of the database server
     * @param string $dbDriver Name of the doctrine database driver (pdo_mysql, pdo_pgsql, etc)
     */
    public function __construct($dbName, $dbUser, $dbPass, $dbHost, $dbPort, $dbDriver)
    {
        try {
            $config = new \Doctrine\DBAL\Configuration();

            $params = [
                'dbname' => $dbName,
                'user' => $dbUser,
                'password' => $dbPass,
                'host' => $dbHost,
                'port' => $dbPort,
                'driver' => $dbDriver,
                'charset' => 'utf8',
                'driverOptions' => [1002 => 'SET NAMES utf8'],
            ];

            $this->dbCon = DriverManager::getConnection($params, $config);
        } catch (\Exception $e) {
            throw new DbConnectionException($e->getMessage());
        }
    }

    /**
     * Destruktor.
     */
    public function __destruct()
    {
        $this->dbCon = null;
    }

    /**
     * Commits the current transaction.
     */
    public function commitTransaction()
    {
        try {
            $this->dbCon->commit();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * Creates a database.
     *
     * @param string $dbName Name of the database to create
     *
     * @throws DbException
     */
    public function createDatabase($dbName)
    {
        $driver = $this->dbCon->getDriver();
        if ($driver instanceof \Doctrine\DBAL\Driver\PDOMySql\Driver) {
            $stmt = $this->dbCon->prepare('create database `'.$dbName.'` /*!40100 COLLATE \'utf8_general_ci\' */');
        } elseif ($driver instanceof \Doctrine\DBAL\Driver\PDOPgSql\Driver) {
            $stmt = $this->dbCon->prepare('create database "'.$dbName.'" ENCODING = \'UTF8\' template=template0');
        } else {
            throw new \Exception('driver not supported yet');
        }
        try {
            $stmt->execute();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * Creates a user.
     *
     * @param string $dbName Name of the database to create
     *
     * @throws DbException
     */
    public function createUser($username, $password)
    {
        $stmt = $this->dbCon->prepare('CREATE USER ? IDENTIFIED BY ?');
        $stmt->bindParam(1, $username, \PDO::PARAM_STR);
        $stmt->bindParam(2, $password, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * Set DB-Permissions.
     *
     * @throws DbException
     */
    public function setDbPermissions($database, $username)
    {
        $permissions = ['SELECT', 'EXECUTE', 'SHOW VIEW', 'ALTER', 'ALTER ROUTINE', 'CREATE', 'CREATE ROUTINE',
                'CREATE TEMPORARY TABLES', 'CREATE VIEW', 'DELETE', 'DROP', 'EVENT', 'INDEX', 'INSERT',
                'REFERENCES', 'TRIGGER', 'UPDATE', 'LOCK TABLES', ];

        $query = 'GRANT '.implode(', ', $permissions).' on `'.$database.'`.* to `'.$username.'`';
        $this->dbCon->exec($query);
        $this->dbCon->exec('FLUSH PRIVILEGES;');
    }

    /**
     * Checks if a table exists.
     *
     * @param string $table Table name to check for
     *
     * @return bool true if table exists, false if not
     */
    public function doesTableExist($table)
    {
        $stmt = $this->dbCon->prepare('select exists (select null from information_schema.tables where table_name=?)');
        $stmt->bindParam(1, $table, \PDO::PARAM_STR);
        try {
            $stmt->execute();

            return $stmt->fetchColumn(0);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param string $statement
     *
     * @return int the number of affected rows
     *
     * @throws DbException
     */
    public function execSQL($query)
    {
        try {
            return $this->dbCon->exec($query);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * Returns the DB connection object.
     *
     * @return \Doctrine\DBAL\Connection DB connection object
     */
    public function getConnection()
    {
        return $this->dbCon;
    }

    /**
     * Returns the SQL type name for a DbDataType.
     *
     * @param string $datatype The DbDataType to check for
     *
     * @return string The matching SQL type name
     */
    public function getDbDatatype($datatype)
    {
        $pf = $this->dbCon->getDatabasePlatform();

        switch ($datatype) {
            case DbDataType::Decimal:
                return $pf->getFloatDeclarationSQL([]);
            case DbDataType::Text:
                return $pf->getClobTypeDeclarationSQL([]);
            case DbDataType::UUID:
                return $pf->getGuidTypeDeclarationSQL([]);
            case DbDataType::Boolean:
                return $pf->getBooleanTypeDeclarationSQL([]);
            case DbDataType::Integer:
                return $pf->getIntegerTypeDeclarationSQL([]);
            case DbDataType::Date:
                return $pf->getDateTypeDeclarationSQL([]);
            case DbDataType::Varchar:
                return $pf->getVarcharTypeDeclarationSQL([]);
            case DbDataType::Time:
                return $pf->getTimeTypeDeclarationSQL([]);
            case DbDataType::Timestamp:
                return $pf->getDateTimeTypeDeclarationSQL([]);
            default:
                return $datatype;
        }
    }

    /**
     * Returns the last inserted id for a table.
     *
     * @param string $tableName The name of the database table
     * @param string $idCol     The name of the id column
     *
     * @return string a string representation of the last inserted ID
     */
    public function getLastInsertId($tableName, $idCol)
    {
        $seq = $tableName.'_'.$idCol.'_seq';

        return $this->dbCon->lastInsertId($seq);
    }

    /**
     * Returns the PDO type for a value / DbDataType pair.
     *
     * @param mixed $value The value to check for
     * @param $type The DbDataType to check for
     *
     * @return int The matching PDO type
     */
    public function getPDOType($value, $type)
    {
        if (is_null($value)) {
            return \PDO::PARAM_NULL;
        }

        switch ($type) {
            // ToDo: check Param_Bool for postgres. Previously PARAM_STR - does not work for MySQL
            case DbDataType::Boolean:
                return \PDO::PARAM_BOOL;
            case DbDataType::Char:
                return \PDO::PARAM_STR;
            case DbDataType::Date:
                return \PDO::PARAM_STR;
            case DbDataType::Decimal:
                return \PDO::PARAM_STR;
            case DbDataType::Integer:
                return \PDO::PARAM_INT;
            case DbDataType::Text:
                return \PDO::PARAM_STR;
            case DbDataType::Time:
                return \PDO::PARAM_STR;
            case DbDataType::Timestamp:
                return \PDO::PARAM_STR;
            case DbDataType::UUID:
                return \PDO::PARAM_STR;
            case DbDataType::Varchar:
                return \PDO::PARAM_STR;
        }
    }

    /**
     * Returns the database definition for autoincrement types.
     *
     * @return string Name of the type
     */
    public function getSerialType()
    {
        $pf = $this->dbCon->getDatabasePlatform();

        return $pf->getIntegerTypeDeclarationSQL(['autoincrement' => true]);
    }

    /**
     * Load column-definition for table from database server
     * Used for comparing the table-structure with an entity-description.
     *
     * @param string $table Name of the table
     *
     * @return array Array with column informations
     */
    public function loadFieldData($table)
    {
        $stmt = $this->dbCon->prepare('select column_name, is_nullable, data_type, character_maximum_length '.
            'from INFORMATION_SCHEMA.COLUMNS where table_name = ?');
        $stmt->bindParam(1, $table, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }

        $i = 0;
        foreach ($stmt as $row) {
            $dbFieldData[$i]['column_name'] = $row['column_name'];
            $dbFieldData[$i]['is_primary'] = false;
            if ('NO' == strtoupper($row['is_nullable'])) {
                $dbFieldData[$i]['is_nullable'] = false;
            } else {
                $dbFieldData[$i]['is_nullable'] = true;
            }
            $dbFieldData[$i]['dbType'] = $this->mapDbType($row['dbType']);
            $dbFieldData[$i]['dbVarCharLen'] = $row['character_maximum_length'];
            $dbFieldData[$i]['syncState'] = '';
            ++$i;
        }
        $stmt = $this->dbCon->prepare('select column_name from INFORMATION_SCHEMA.key_column_usage '.
            "where table_name = ? and constraint_name like '%_pkey'");
        $stmt->bindParam(1, $table, \PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }

        foreach ($stmt as $row) {
            foreach ($dbFieldData as &$field) {
                if ($field['column_name'] == $row['column_name']) {
                    $field['is_primary'] = true;
                }
            }
            unset($field);
        }

        return $dbFieldData;
    }

    /**
     * Prepares an SQL statement.
     *
     * @param string $statement the SQL statement to prepare
     *
     * @return \Doctrine\DBAL\Driver\Statement the prepared statement
     */
    public function prepare($sql)
    {
        try {
            return $this->dbCon->prepare($sql);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * Executes an SQL statement, returning a resultset as a statement object.
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function query($query)
    {
        try {
            return $this->dbCon->query($query);
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * Quotes a string so it can be safely used as a column name.
     *
     * @param string $columnName the column name to be quoted
     *
     * @return string the quoted name
     */
    public function quoteColumnName($columnName)
    {
        return $this->dbCon->quoteIdentifier($columnName);
    }

    /**
     * Method for quoting the table-name (not in use here).
     *
     * @param string $tableName The table name to quote
     *
     * @return string The quoted table-name
     */
    public function quoteTableName($tableName)
    {
        return $tableName;
    }

    /**
     * Quotes a value for using it in sql requests.
     *
     * @param string      $value The value to quote
     * @param string|null $type  the type of the value
     *
     * @return string The quoted value
     */
    public function quoteValue($value, $type)
    {
        if (is_null($value)) {
            return $this->dbCon->quote(null, \PDO::PARAM_NULL);
        }
        if (DbDataType::Boolean == $type) {
            if ((1 === $value) || (true === $value) || ('TRUE' === strtoupper($value))) {
                return 'TRUE';
            } else {
                return 'FALSE';
            }
        } else {
            return $this->dbCon->quote($value, $this->getPDOType($value, $type));
        }
    }

    /**
     * Rollback any changes during the current transaction.
     */
    public function rollbackTransaction()
    {
        try {
            $this->dbCon->rollback();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /**
     * Starts a transaction.
     */
    public function startTransaction()
    {
        try {
            $this->dbCon->beginTransaction();
        } catch (\Exception $e) {
            throw new DbException($e->getMessage());
        }
    }
}
