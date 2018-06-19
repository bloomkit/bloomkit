<?php

namespace Bloomkit\Core\Database;

use Doctrine\DBAL\Connection;
use Bloomkit\Core\Database\Exceptions\DbException;

class SequenceGenerator
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $dbCon;

    /**
     * @var string
     */
    private $generatorTableName;

    /**
     * @var array
     */
    private $sequences = [];

    /**
     * Constructor.
     *
     * @param Connection $dbCon              Database connection object
     * @param string     $generatorTableName Name of the table with the stored sequence-counters
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct($dbCon, $generatorTableName = 'sequences')
    {
        $params = $dbCon->getParams();
        if ($params['driver'] == 'pdo_sqlite') {
            throw new DbException('Cannot use SequenceGenerator with SQLite.');
        }
        $this->dbCon = $dbCon;
        $this->generatorTableName = $generatorTableName;
    }

    /**
     * Generates the next unused value for the given sequence name.
     *
     * @param string $sequenceName
     *
     * @return int
     *
     * @throws DbException
     */
    public function nextValue($sequenceName)
    {
        if (isset($this->sequences[$sequenceName])) {
            $value = $this->sequences[$sequenceName]['value'];
            ++$this->sequences[$sequenceName]['value'];
            if ($this->sequences[$sequenceName]['value'] >= $this->sequences[$sequenceName]['max']) {
                unset($this->sequences[$sequenceName]);
            }

            return $value;
        }

        $this->dbCon->beginTransaction();

        try {
            $platform = $this->dbCon->getDatabasePlatform();
            $sql = 'SELECT sequence_value, sequence_increment_by FROM '.
                $platform->appendLockHint($this->generatorTableName, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE).
                ' WHERE sequence_name = ? '.$platform->getWriteLockSQL();
            $stmt = $this->dbCon->executeQuery($sql, [$sequenceName]);

            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $row = array_change_key_case($row, CASE_LOWER);

                $value = $row['sequence_value'];
                ++$value;

                if ($row['sequence_increment_by'] > 1) {
                    $this->sequences[$sequenceName] = [
                        'value' => $value,
                        'max' => $row['sequence_value'] + $row['sequence_increment_by'],
                    ];
                }

                $sql = 'UPDATE '.$this->generatorTableName.' SET sequence_value = sequence_value + sequence_increment_by '.
                    'WHERE sequence_name = ? AND sequence_value = ?';
                $rows = $this->dbCon->executeUpdate($sql, [$sequenceName, $row['sequence_value']]);

                if ($rows != 1) {
                    throw new DbException('Race-condition detected while updating sequence. Aborting generation');
                }
            } else {
                $this->dbCon->insert($this->generatorTableName, [
                    'sequence_name' => $sequenceName,
                    'sequence_value' => 1,
                    'sequence_increment_by' => 1,
                ]);
                $value = 1;
            }

            $this->dbCon->commit();
        } catch (\Exception $e) {
            $this->dbCon->rollback();
            throw new DbException('Error generating sequence generator id, aborted generation: '.$e->getMessage(), 0, $e);
        }

        return $value;
    }
}
