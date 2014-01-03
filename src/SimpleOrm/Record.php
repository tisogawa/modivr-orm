<?php

namespace SimpleOrm;

use PDOException;
use SimpleOrm\Exception\RecordException;

/**
 * Class Record
 * @package SimpleOrm
 */
abstract class Record
{
    /**
     * @var string
     */
    protected static $tableClassName = '';
    /**
     * @var array
     */
    protected static $pkColumns = array();
    /**
     * @var array
     */
    protected $columns = array();
    /**
     * @var bool
     */
    protected $new = true;

    /**
     * @return string
     */
    public static function getTableClassName()
    {
        return static::$tableClassName;
    }

    /**
     * @return array
     */
    public static function getPkColumns()
    {
        return static::$pkColumns;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->columns;
    }

    /**
     * @param array $array
     * @return Record
     */
    public function fromArray(array $array)
    {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * @return array
     * @throws RecordException
     */
    public function getPrimaryKey()
    {
        $result = array();
        foreach (static::$pkColumns as $column) {
            if (!array_key_exists($column, $this->columns)) {
                throw new RecordException(sprintf(
                    'The primary key column %s is not defined in columns property.',
                    $column
                ));
            }
            $result[$column] = $this->columns[$column];
        }
        return $result;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * @param $value
     * @return Record
     */
    public function setNew($value)
    {
        $this->new = $value;
        return $this;
    }

    /**
     * @return Record
     * @throws PDOException
     * @throws RecordException
     */
    public function save()
    {
        if ($this->isNew() &&
            array_key_exists('created_at', $this->columns) &&
            $this->columns['created_at'] === null
        ) {
            $this->created_at = time();
        }
        if (array_key_exists('updated_at', $this->columns)) {
            $this->updated_at = time();
        }
        if ($this->isNew()) {
            Connection::insert($this->getTableName(),
                $this->columns);
            if (count(static::$pkColumns) == 1) {
                $pkColumn = static::$pkColumns[0];
                $this->$pkColumn = Connection::getConnection()->lastInsertId();
            }
        } else {
            $pks = $this->getPrimaryKey();
            foreach ($pks as $column => $value) {
                if (!$value) {
                    throw new RecordException(sprintf(
                        'Value of the primary key column %s is not set.',
                        $column
                    ));
                }
            }
            Connection::update(
                $this->getTableName(),
                $this->columns,
                sprintf('WHERE %s', Util::createAndClauseStringFromArray($pks))
            );
        }
        $this->setNew(false);
        return $this;
    }

    /**
     * @return Record
     * @throws PDOException
     * @throws RecordException
     */
    public function delete()
    {
        if ($this->isNew()) {
            throw new RecordException(
                'The object is not saved yet.');
        }
        $pks = $this->getPrimaryKey();
        foreach ($pks as $column => $value) {
            if (!$value) {
                throw new RecordException(sprintf(
                    'Value of the primary key column %s is not set.',
                    $column
                ));
            }
        }
        Connection::delete(
            $this->getTableName(),
            $pks,
            sprintf('WHERE %s', Util::createAndClauseStringFromArray($pks))
        );
        return $this;
    }

    /**
     * @param $name
     * @return int|null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->columns)) {
            $value = $this->columns[$name];
            if (ctype_digit((string)$value)) {
                $value = (int)$value;
            }
            if ($value !== null && preg_match('/_at$/', $name)) {
                $value = strtotime($value);
            }
            return $value;
        }
        return null;
    }

    /**
     * @param $name
     * @param $value
     * @return Record
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->columns)) {
            if (is_int($value) && preg_match('/_at$/', $name)) {
                $value = date('Y-m-d H:i:s', $value);
            }
            if (ctype_digit((string)$value)) {
                $value = (int)$value;
            }
            $this->columns[$name] = $value;
        }
        return $this;
    }

    /**
     * @param $method
     * @param array $arguments
     * @return $this|mixed
     * @throws RecordException
     */
    public function __call($method, array $arguments)
    {
        if (preg_match('/^(get|set)(.+)$/', $method, $matches)) {
            $column = Util::underscore($matches[2]);
            switch ($matches[1]) {
                case 'set':
                    $this->$column = $arguments[0];
                    return $this;
                    break;
                case 'get':
                default:
                    return $this->$column;
            }
        }
        throw new RecordException(sprintf(
            'Call to undefined method %s::%s()',
            get_class($this),
            $method
        ));
    }

    /**
     * @return string
     */
    protected function getTableName()
    {
        return call_user_func(array(
            static::$tableClassName,
            'getTableName'));
    }
}
