<?php

use ModivrOrm\ConfigurationInterface;
use ModivrOrm\Record;
use ModivrOrm\Table;

/**
 * Class IntentionalException
 * Exception to be thrown intentionally in the tests.
 */
class IntentionalException extends Exception
{
}

/**
 * Class PackageTestConfig
 */
class PackageTestConfig implements ConfigurationInterface
{
    public function getDsn()
    {
        return sprintf('mysql:host=localhost;dbname=%s', MODIVR_ORM_TEST_DB_NAME);
    }

    public function getUsername()
    {
        return 'root';
    }

    public function getPassword()
    {
        return '';
    }

    public function getOptions()
    {
        return array(
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );
    }
}

/**
 * Class PackageTest1Table
 *
 * @method PackageTest1[] findById()
 * @method PackageTest1 findOneById()
 * @method PackageTest1[] findByName()
 * @method PackageTest1 findOneByName()
 * @method PackageTest1[] findByCreatedAt()
 * @method PackageTest1 findOneByCreatedAt()
 * @method PackageTest1[] findByUpdatedAt()
 * @method PackageTest1 findOneByUpdatedAt()
 */
class PackageTest1Table extends Table
{
    protected static $recordClassName = 'PackageTest1';
    protected static $tableName = 'rdb_package_test_1';
}

/**
 * Class PackageTest1
 *
 * @method PackageTest1 setId()
 * @method int getId()
 * @method PackageTest1 setName()
 * @method string getName()
 * @method PackageTest1 setCreatedAt()
 * @method int getCreatedAt()
 * @method PackageTest1 setUpdatedAt()
 * @method int getUpdatedAt()
 */
class PackageTest1 extends Record
{
    protected static $tableClassName = 'PackageTest1Table';
    protected static $pkColumns = array('id');
    protected $columns = array(
        'id' => null,
        'name' => null,
        'created_at' => null,
        'updated_at' => null);
}

/**
 * Class PackageTest2Table
 *
 * @method PackageTest2[] findByUserId()
 * @method PackageTest2 findOneByUserId()
 * @method PackageTest2[] findByItemId()
 * @method PackageTest2 findOneByItemId()
 * @method PackageTest2[] findByCount()
 * @method PackageTest2 findOneByCount()
 * @method PackageTest2[] findByCreatedAt()
 * @method PackageTest2 findOneByCreatedAt()
 * @method PackageTest2[] findByUpdatedAt()
 * @method PackageTest2 findOneByUpdatedAt()
 *
 * @method PackageTest1 findOneByUserIdAndItemId()
 */
class PackageTest2Table extends Table
{
    protected static $recordClassName = 'PackageTest2';
    protected static $tableName = 'rdb_package_test_2';
}

/**
 * Class PackageTest2
 *
 * @method PackageTest2 setUserId()
 * @method int getUserId()
 * @method PackageTest2 setItemId()
 * @method int getItemId()
 * @method PackageTest2 setCount()
 * @method int getCount()
 * @method PackageTest2 setCreatedAt()
 * @method int getCreatedAt()
 * @method PackageTest2 setUpdatedAt()
 * @method int getUpdatedAt()
 */
class PackageTest2 extends Record
{
    protected static $tableClassName = 'PackageTest2Table';
    protected static $pkColumns = array('user_id', 'item_id');
    protected $columns = array(
        'user_id' => null,
        'item_id' => null,
        'count' => null,
        'created_at' => null,
        'updated_at' => null);
}
