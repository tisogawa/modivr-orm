<?php

namespace SimpleOrm;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Class Connection
 * @package SimpleOrm
 */
final class Connection
{
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;

    /**
     * @var PDO
     */
    protected static $pdo;
    /**
     * @var ConfigurationInterface
     */
    protected static $config;

    /**
     * @param ConfigurationInterface $config
     */
    public static function setConfig(ConfigurationInterface $config)
    {
        self::$config = $config;
    }

    /**
     * @return PDO
     * @throws RuntimeException
     */
    public static function getConnection()
    {
        if (!isset(self::$pdo)) {
            if (!isset(self::$config)) {
                throw new RuntimeException('Configuration is not set.');
            }
            self::$pdo = new PDO(
                self::$config->getDsn(),
                self::$config->getUsername(),
                self::$config->getPassword(),
                self::$config->getOptions()
            );
        }
        return self::$pdo;
    }

    /**
     * @param $sql
     * @param array $params
     * @return PDOStatement
     */
    public static function select($sql, array $params)
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }

    /**
     * @param $table
     * @param array $params
     * @return bool
     */
    public static function insert($table, array $params)
    {
        foreach ($params as $key => $value) {
            if ($value === '') {
                $params[$key] = null;
            }
        }
        $stmt = self::getWriteStatement(
            $table,
            $params
        );
        return $stmt->execute($params);
    }

    /**
     * @param $table
     * @param array $params
     * @param $condition
     * @return bool
     */
    public static function update($table, array $params, $condition)
    {
        foreach ($params as $key => $value) {
            if ($value === '') {
                $params[$key] = null;
            }
        }
        $stmt = self::getWriteStatement(
            $table,
            $params,
            self::UPDATE,
            $condition
        );
        return $stmt->execute($params);
    }

    /**
     * @param $table
     * @param array $params
     * @param $condition
     * @return bool
     */
    public static function delete($table, array $params, $condition)
    {
        $stmt = self::getWriteStatement(
            $table,
            $params,
            self::DELETE,
            $condition
        );
        return $stmt->execute($params);
    }

    /**
     * @param $table
     * @param array $params
     * @param int $mode
     * @param string $condition
     * @return PDOStatement
     */
    public static function getWriteStatement($table, array $params, $mode = self::INSERT, $condition = '')
    {
        $param_keys = array();
        foreach ($params as $key => $value) {
            $param_keys[] = str_replace(':', '', $key);
        }
        switch ($mode) {
            case self::DELETE:
                $sql = "DELETE FROM $table $condition";
                break;
            case self::UPDATE:
                $array = array();
                foreach ($param_keys as $key) {
                    $array[] = "$key = :$key";
                }
                $param = implode(', ', $array);
                $sql = "UPDATE $table SET $param $condition";
                break;
            case self::INSERT:
            default:
                $param_key = implode(', ', $param_keys);
                $param_value = ':' . implode(', :', $param_keys);
                $sql = "INSERT INTO $table ($param_key) VALUES ($param_value)";
        }
        return self::getConnection()->prepare($sql);
    }

    /**
     * @return bool
     */
    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * @return bool
     */
    public static function commit()
    {
        return self::getConnection()->commit();
    }

    /**
     * @return bool
     */
    public static function rollback()
    {
        return self::getConnection()->rollback();
    }
}
