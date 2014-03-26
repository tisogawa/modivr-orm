<?php

use ModivrOrm\ConfigurationInterface;
use ModivrOrm\Connection;
use ModivrOrm\Record;
use ModivrOrm\Table;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class IntentionalException
 *
 * Exception to be thrown intentionally in the following tests.
 */
class IntentionalException extends Exception
{
}

/**
 * ---------------------------------------------------------------------
 * Prepare a database
 * ---------------------------------------------------------------------
 */

define('SIMPLE_ORM_TEST_DB_NAME', 'simple_orm_test');

/**
 * Class PackageTestConfig
 */
class PackageTestConfig implements ConfigurationInterface
{
    public function getDsn()
    {
        return sprintf('mysql:host=localhost;dbname=%s', SIMPLE_ORM_TEST_DB_NAME);
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

Connection::setConfig(new PackageTestConfig());
Connection::getConnection()->exec(
    'DROP TABLE IF EXISTS rdb_package_test_1'
);
Connection::getConnection()->exec(
    'CREATE TABLE IF NOT EXISTS rdb_package_test_1 (
        id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name              VARCHAR(255) NOT NULL,
        created_at        DATETIME,
        updated_at        DATETIME,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8'
);

/**
 * ---------------------------------------------------------------------
 * PackageTest1 - Single primary key
 * ---------------------------------------------------------------------
 */

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

/** @var PackageTest1[] $records */
$records = PackageTest1Table::getInstance()->findBy();
assert(empty($records));

/**
 * Create
 */
/** @var PackageTest1 $record */
$record = new PackageTest1();
$record->setName('test1');

$pks = $record->getPrimaryKey();
$pk = $pks['id'];
$name = $record->getName();
$created_at = $record->getCreatedAt();
$updated_at = $record->getUpdatedAt();

assert($record instanceof PackageTest1);
assert($record->isNew());
assert($name === 'test1');
assert($pk === null);
assert($created_at === null);
assert($updated_at === null);

/**
 * Save
 */
$record->save();

$pks = $record->getPrimaryKey();
$pk = $pks['id'];
$created_at = $record->getCreatedAt();
$updated_at = $record->getUpdatedAt();

assert($record instanceof PackageTest1);
assert(!$record->isNew());
assert(ctype_digit((string)$pk));
assert(ctype_digit((string)$created_at));
assert(ctype_digit((string)$updated_at));

/**
 * Table::find()
 */
unset($record);
$record = PackageTest1Table::getInstance()->find($pk);

$pks = $record->getPrimaryKey();
$pk_loaded = $pks['id'];
$name_loaded = $record->getName();
$created_at_loaded = $record->getCreatedAt();
$updated_at_loaded = $record->getUpdatedAt();

assert($record instanceof PackageTest1);
assert(!$record->isNew());
assert($pk_loaded === $pk);
assert($name_loaded === $name);
assert($created_at_loaded === $created_at);
assert($updated_at_loaded === $updated_at);

/**
 * Table::findOneBy()
 */
unset($record);
$record = PackageTest1Table::getInstance()->findOneBy('id', $pk);

$pks = $record->getPrimaryKey();
$pk_loaded = $pks['id'];
$name_loaded = $record->getName();
$created_at_loaded = $record->getCreatedAt();
$updated_at_loaded = $record->getUpdatedAt();

assert($record instanceof PackageTest1);
assert(!$record->isNew());
assert($pk_loaded === $pk);
assert($name_loaded === $name);
assert($created_at_loaded === $created_at);
assert($updated_at_loaded === $updated_at);

/**
 * Table::findOneBy*()
 */
unset($record);
$record = PackageTest1Table::getInstance()->findOneById($pk);

$pks = $record->getPrimaryKey();
$pk_loaded = $pks['id'];
$name_loaded = $record->getName();
$created_at_loaded = $record->getCreatedAt();
$updated_at_loaded = $record->getUpdatedAt();

assert($record instanceof PackageTest1);
assert(!$record->isNew());
assert($pk_loaded === $pk);
assert($name_loaded === $name);
assert($created_at_loaded === $created_at);
assert($updated_at_loaded === $updated_at);

/**
 * Update
 * Sleep 2 seconds before update to ensure updated_at is updated.
 */
sleep(2);
$record->setName('test2')->save();
unset($record);
$record = PackageTest1Table::getInstance()->find($pk);

assert($record->getName() === 'test2');
$created_at_loaded = $record->getCreatedAt();
$updated_at_loaded = $record->getUpdatedAt();
assert($created_at_loaded === $created_at);
assert($updated_at_loaded !== $updated_at);

/**
 * Delete
 */
$record->delete();
unset($record);
$record = PackageTest1Table::getInstance()->find($pk);

assert($record === null);

/**
 * Table::findBy*()
 */
unset($record);
for ($i = 1; $i <= 10; $i++) {
    $record = new PackageTest1();
    $record
        ->setName('そうだ京都行こう')
        ->save();
}

$records = PackageTest1Table::getInstance()->findByName(
    'そうだ京都行こう');

assert(is_array($records));
assert(count($records) == 10);
foreach ($records as $record) {
    assert($record instanceof PackageTest1);
    assert($record->getName() == 'そうだ京都行こう');
}

/**
 * Table::findBy()
 */
unset($records);
$records = PackageTest1Table::getInstance()->findBy(
    null, null,
    array('order_by' => 'id DESC'));

assert(is_array($records));
assert(count($records) == 10);
foreach ($records as $record) {
    assert($record instanceof PackageTest1);
    assert($record->getName() == 'そうだ京都行こう');
}
$lastRecord = $records[0];

unset($records);
$records = PackageTest1Table::getInstance()->findBy(
    'name', 'そうだ京都行こう',
    array('order_by' => 'id DESC', 'limit' => 5));

assert(is_array($records));
assert(count($records) == 5);
foreach ($records as $record) {
    assert($record instanceof PackageTest1);
    assert($record->getName() == 'そうだ京都行こう');
}
assert($records[0]->getId() == $lastRecord->getId());

unset($records);
$records = PackageTest1Table::getInstance()->findBy(
    array('name' => 'そうだ京都行こう'), null,
    array('order_by' => 'id DESC', 'limit' => 5));

assert(is_array($records));
assert(count($records) == 5);
foreach ($records as $record) {
    assert($record instanceof PackageTest1);
    assert($record->getName() == 'そうだ京都行こう');
}
assert($records[0]->getId() == $lastRecord->getId());

unset($records);
$records = PackageTest1Table::getInstance()->findBy(
    'name = \'そうだ京都行こう\'', null,
    array('order_by' => 'id DESC', 'limit' => 5));

assert(is_array($records));
assert(count($records) == 5);
foreach ($records as $record) {
    assert($record instanceof PackageTest1);
    assert($record->getName() == 'そうだ京都行こう');
}
assert($records[0]->getId() == $lastRecord->getId());

/**
 * Transaction to be failed
 */
unset($records);
$records = PackageTest1Table::getInstance()->findBy();
try {
    Connection::beginTransaction();
    foreach ($records as $record) {
        $record->delete();
    }
    throw new IntentionalException(
        'An intentional exception to force rollback');
//    Connection::commit();
} catch (IntentionalException $e) {
    Connection::rollback();
}
unset($records);
$records = PackageTest1Table::getInstance()->findBy();

assert(is_array($records));
assert(count($records) == 10);
foreach ($records as $record) {
    assert($record instanceof PackageTest1);
    assert($record->getName() == 'そうだ京都行こう');
}

/**
 * Transaction to be succeeded
 */
try {
    Connection::beginTransaction();
    foreach ($records as $record) {
        $record->delete();
    }
    Connection::commit();
} catch (Exception $e) {
    Connection::rollback();
}
unset($records);
$records = PackageTest1Table::getInstance()->findBy();

assert(is_array($records));
assert(empty($records));

Connection::getConnection()->exec(
    'DROP TABLE IF EXISTS rdb_package_test_1'
);

/**
 * ---------------------------------------------------------------------
 * PackageTest2 - Multiple primary key
 * ---------------------------------------------------------------------
 */

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

Connection::getConnection()->exec(
    'DROP TABLE IF EXISTS rdb_package_test_2');
Connection::getConnection()->exec(
    'CREATE TABLE IF NOT EXISTS rdb_package_test_2 (
        user_id           INT UNSIGNED NOT NULL,
        item_id           INT UNSIGNED NOT NULL,
        count             INT UNSIGNED NOT NULL,
        created_at        DATETIME,
        updated_at        DATETIME,
        PRIMARY KEY (user_id, item_id)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8');

/** @var PackageTest2[] $records */
$records = PackageTest2Table::getInstance()->findBy();
assert(empty($records));

/**
 * Create
 */
$random_user_id = mt_rand();
$random_item_id = mt_rand();
/** @var PackageTest2 $record */
$record = new PackageTest2();
$record->setUserId($random_user_id)->setItemId($random_item_id)->setCount(10);

$pks = $record->getPrimaryKey();
$user_id = $pks['user_id'];
$item_id = $pks['item_id'];
$count = $record->getCount();
$created_at = $record->getCreatedAt();
$updated_at = $record->getUpdatedAt();

assert($record instanceof PackageTest2);
assert($record->isNew());
assert($user_id === $random_user_id);
assert($item_id === $random_item_id);
assert($count === 10);
assert($created_at === null);
assert($updated_at === null);

/**
 * Save
 */
$record->save();

$pks = $record->getPrimaryKey();
$user_id = $pks['user_id'];
$item_id = $pks['item_id'];
$count = $record->getCount();
$created_at = $record->getCreatedAt();
$updated_at = $record->getUpdatedAt();

assert($record instanceof PackageTest2);
assert(!$record->isNew());
assert(ctype_digit((string)$created_at));
assert(ctype_digit((string)$updated_at));

/**
 * Table::find()
 */
unset($record);
$record = PackageTest2Table::getInstance()->find($user_id, $item_id);

$pks = $record->getPrimaryKey();
$user_id_loaded = $pks['user_id'];
$item_id_loaded = $pks['item_id'];
$count_loaded = $record->getCount();
$created_at_loaded = $record->getCreatedAt();
$updated_at_loaded = $record->getUpdatedAt();

assert($record instanceof PackageTest2);
assert(!$record->isNew());
assert($user_id_loaded === $user_id);
assert($item_id_loaded === $item_id);
assert($count_loaded === $count);
assert($created_at_loaded === $created_at);
assert($updated_at_loaded === $updated_at);

/**
 * Table::findOneBy()
 */
unset($record);
$record = PackageTest2Table::getInstance()->findOneBy(array('user_id', 'item_id'), array($user_id, $item_id));

$pks = $record->getPrimaryKey();
$user_id_loaded = $pks['user_id'];
$item_id_loaded = $pks['item_id'];
$count_loaded = $record->getCount();
$created_at_loaded = $record->getCreatedAt();
$updated_at_loaded = $record->getUpdatedAt();

assert($record instanceof PackageTest2);
assert(!$record->isNew());
assert($user_id_loaded === $user_id);
assert($item_id_loaded === $item_id);
assert($count_loaded === $count);
assert($created_at_loaded === $created_at);
assert($updated_at_loaded === $updated_at);

/**
 * Table::findOneBy*()
 */
unset($record);
$record = PackageTest2Table::getInstance()->findOneByUserIdAndItemId($user_id, $item_id);

$pks = $record->getPrimaryKey();
$user_id_loaded = $pks['user_id'];
$item_id_loaded = $pks['item_id'];
$count_loaded = $record->getCount();
$created_at_loaded = $record->getCreatedAt();
$updated_at_loaded = $record->getUpdatedAt();

assert($record instanceof PackageTest2);
assert(!$record->isNew());
assert($user_id_loaded === $user_id);
assert($item_id_loaded === $item_id);
assert($count_loaded === $count);
assert($created_at_loaded === $created_at);
assert($updated_at_loaded === $updated_at);

/**
 * Update
 * Sleep 2 seconds before update to ensure updated_at is updated.
 */
sleep(2);
$record->setCount($record->getCount() * 2)->save();
unset($record);
$record = PackageTest2Table::getInstance()->find($user_id, $item_id);

assert($record->getCount() === 20);
$created_at_loaded = $record->getCreatedAt();
$updated_at_loaded = $record->getUpdatedAt();
assert($created_at_loaded === $created_at);
assert($updated_at_loaded !== $updated_at);

/**
 * Delete
 */
$record->delete();
unset($record);
$record = PackageTest2Table::getInstance()->find($user_id, $item_id);

assert($record === null);

/**
 * Table::findBy*()
 */
unset($record);

for ($i = 1; $i <= 10; $i++) {
    $record = new PackageTest2();
    $record
        ->setUserId(1)
        ->setItemId($i)
        ->setCount($i)
        ->save();
}

$records = PackageTest2Table::getInstance()->findByUserId(1);

assert(is_array($records));
assert(count($records) == 10);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

unset($records);
$records = PackageTest2Table::getInstance()->findByItemId(5);

assert(is_array($records));
assert(count($records) == 1);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

/**
 * Table::findBy()
 */
unset($records);
$records = PackageTest2Table::getInstance()->findBy();

assert(is_array($records));
assert(count($records) == 10);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

unset($records);
$records = PackageTest2Table::getInstance()->findBy('user_id', 1);

assert(is_array($records));
assert(count($records) == 10);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

unset($records);
$records = PackageTest2Table::getInstance()->findBy('item_id', 5);

assert(is_array($records));
assert(count($records) == 1);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

unset($records);
$records = PackageTest2Table::getInstance()->findBy(array('user_id' => 1));

assert(is_array($records));
assert(count($records) == 10);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

unset($records);
$records = PackageTest2Table::getInstance()->findBy(array('item_id' => 5));

assert(is_array($records));
assert(count($records) == 1);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

unset($records);
$records = PackageTest2Table::getInstance()->findBy('user_id = 1');

assert(is_array($records));
assert(count($records) == 10);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

unset($records);
$records = PackageTest2Table::getInstance()->findBy('item_id = 5');

assert(is_array($records));
assert(count($records) == 1);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

unset($records);
$records = PackageTest2Table::getInstance()->findBy('count > 5');

assert(is_array($records));
assert(count($records) == 5);
foreach ($records as $record) {
    assert($record instanceof PackageTest2);
}

Connection::getConnection()->exec(
    'DROP TABLE IF EXISTS rdb_package_test_2'
);
