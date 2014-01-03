<?php

namespace SimpleOrm;

use PDOStatement;
use SimpleOrm\Exception\TableException;

/**
 * Class Table
 * @package SimpleOrm
 */
abstract class Table
{
    /**
     * @var string
     */
    protected static $recordClassName = '';
    /**
     * @var string
     */
    protected static $tableName = '';

    /**
     * @return static
     */
    public static function getInstance()
    {
        $instance = new static();
        return $instance;
    }

    /**
     * @return string
     */
    public static function getRecordClassName()
    {
        return static::$recordClassName;
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return static::$tableName;
    }

    /**
     * @return null|Record
     * @throws TableException
     */
    public function find()
    {
        $params = func_get_args();
        $pkColumns = call_user_func(array(static::$recordClassName, 'getPkColumns'));
        $pks = @array_combine($pkColumns, $params);
        if (!$pks) {
            throw new TableException(sprintf(
                'The table %s has %d primary key(s). %d given.',
                static::$tableName,
                count($pkColumns),
                count($params)
            ));
        }
        return $this->findOneBy($pks);
    }

    /**
     * @param $columns
     * @param null $values
     * @return null|Record
     */
    public function findOneBy($columns, $values = null)
    {
        $stmt = $this->getStatement($columns, $values, array('limit' => 1));
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        /** @var Record $record */
        $record = new static::$recordClassName();
        $record
            ->fromArray($row)
            ->setNew(false);
        return $record;
    }

    /**
     * @param null $columns
     * @param null $values
     * @param array $options
     * @return array
     */
    public function findBy($columns = null, $values = null, array $options = array())
    {
        $stmt = $this->getStatement($columns, $values, $options);
        $result = array();
        while ($row = $stmt->fetch()) {
            /** @var Record $record */
            $record = new static::$recordClassName();
            $record
                ->fromArray($row)
                ->setNew(false);
            $result[] = $record;
        }
        return $result;
    }

    /**
     * @param $method
     * @param array $arguments
     * @return mixed
     * @throws TableException
     */
    public function __call($method, array $arguments)
    {
        if (preg_match('/^(findBy|findOneBy)(.+)$/', $method, $matches)) {
            $method = $matches[1];
            $params = array();
            $columns = explode('And', $matches[2]);
            foreach ($columns as $index => $column) {
                $column = Util::underscore($column);
                if (!isset($arguments[$index])) {
                    throw new TableException(sprintf(
                        'No corresponding value for the column %s was given.',
                        $column
                    ));
                }
                $params[$column] = $arguments[$index];
            }
            return $this->$method($params);
        }
        throw new TableException(sprintf(
            'Call to undefined method %s::%s()',
            get_class($this),
            $method
        ));
    }

    /**
     * @param null $columns
     * @param null $values
     * @param array $options
     * @return PDOStatement
     * @throws TableException
     */
    protected function getStatement($columns = null, $values = null, array $options = array())
    {
        if (isset($columns)) {
            if (isset($values)) {
                if (!is_array($columns)) {
                    $columns = array($columns);
                }
                if (!is_array($values)) {
                    $values = array($values);
                }
                $params = @array_combine($columns, $values);
                if (!$params) {
                    throw new TableException(sprintf(
                        'Number of elements of the columns (%d) and values (%d) doesn\'t match',
                        count($columns),
                        count($values)
                    ));
                }
                $andClauseString = sprintf('WHERE %s', Util::createAndClauseStringFromArray($params));
            } else {
                if (is_array($columns)) {
                    $params = $columns;
                    $andClauseString = sprintf('WHERE %s', Util::createAndClauseStringFromArray($params));
                } else {
                    $params = array();
                    $andClauseString = "WHERE $columns";
                }
            }
        } else {
            $andClauseString = '';
            $params = array();
        }
        $stmt = Connection::select(sprintf(
            'SELECT * FROM %s %s %s',
            static::$tableName,
            $andClauseString,
            Util::createOptionsStringFromArray($options)
        ), $params);
        return $stmt;
    }
}
