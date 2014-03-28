<?php

use ModivrOrm\Connection;

define('MODIVR_ORM_TEST_DB_NAME', 'modivr_orm_test');

require_once __DIR__ . '/fixtures.php';

class ModivrOrmTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
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
            ) ENGINE=innodb  DEFAULT CHARSET=utf8'
        );

        Connection::getConnection()->exec(
            'DROP TABLE IF EXISTS rdb_package_test_2'
        );
        Connection::getConnection()->exec(
            'CREATE TABLE IF NOT EXISTS rdb_package_test_2 (
                user_id           INT UNSIGNED NOT NULL,
                item_id           INT UNSIGNED NOT NULL,
                count             INT UNSIGNED NOT NULL,
                created_at        DATETIME,
                updated_at        DATETIME,
                PRIMARY KEY (user_id, item_id)
                ) ENGINE=innodb  DEFAULT CHARSET=utf8'
        );
    }

    public function tearDown()
    {
        Connection::getConnection()->exec(
            'DROP TABLE IF EXISTS rdb_package_test_1'
        );
        Connection::getConnection()->exec(
            'DROP TABLE IF EXISTS rdb_package_test_2'
        );
    }

    public function testSinglePrimaryKey()
    {
        /** @var PackageTest1[] $records */
        $records = PackageTest1Table::getInstance()->findBy();
        $this->assertTrue(empty($records));

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

        $this->assertTrue($record instanceof PackageTest1);
        $this->assertTrue($record->isNew());
        $this->assertTrue($name === 'test1');
        $this->assertTrue($pk === null);
        $this->assertTrue($created_at === null);
        $this->assertTrue($updated_at === null);

        /**
         * Save
         */
        $record->save();

        $pks = $record->getPrimaryKey();
        $pk = $pks['id'];
        $created_at = $record->getCreatedAt();
        $updated_at = $record->getUpdatedAt();

        $this->assertTrue($record instanceof PackageTest1);
        $this->assertTrue(!$record->isNew());
        $this->assertTrue(ctype_digit((string)$pk));
        $this->assertTrue(ctype_digit((string)$created_at));
        $this->assertTrue(ctype_digit((string)$updated_at));

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

        $this->assertTrue($record instanceof PackageTest1);
        $this->assertTrue(!$record->isNew());
        $this->assertTrue($pk_loaded === $pk);
        $this->assertTrue($name_loaded === $name);
        $this->assertTrue($created_at_loaded === $created_at);
        $this->assertTrue($updated_at_loaded === $updated_at);

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

        $this->assertTrue($record instanceof PackageTest1);
        $this->assertTrue(!$record->isNew());
        $this->assertTrue($pk_loaded === $pk);
        $this->assertTrue($name_loaded === $name);
        $this->assertTrue($created_at_loaded === $created_at);
        $this->assertTrue($updated_at_loaded === $updated_at);

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

        $this->assertTrue($record instanceof PackageTest1);
        $this->assertTrue(!$record->isNew());
        $this->assertTrue($pk_loaded === $pk);
        $this->assertTrue($name_loaded === $name);
        $this->assertTrue($created_at_loaded === $created_at);
        $this->assertTrue($updated_at_loaded === $updated_at);

        /**
         * Update
         * Sleep 2 seconds before update to ensure updated_at is updated.
         */
        sleep(2);
        $record->setName('test2')->save();
        unset($record);
        $record = PackageTest1Table::getInstance()->find($pk);

        $this->assertTrue($record->getName() === 'test2');
        $created_at_loaded = $record->getCreatedAt();
        $updated_at_loaded = $record->getUpdatedAt();
        $this->assertTrue($created_at_loaded === $created_at);
        $this->assertTrue($updated_at_loaded !== $updated_at);

        /**
         * Delete
         */
        $record->delete();
        unset($record);
        $record = PackageTest1Table::getInstance()->find($pk);

        $this->assertTrue($record === null);

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

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 10);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest1);
            $this->assertTrue($record->getName() == 'そうだ京都行こう');
        }

        /**
         * Table::findBy()
         */
        unset($records);
        $records = PackageTest1Table::getInstance()->findBy(
            null, null,
            array('order_by' => 'id DESC'));

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 10);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest1);
            $this->assertTrue($record->getName() == 'そうだ京都行こう');
        }
        $lastRecord = $records[0];

        unset($records);
        $records = PackageTest1Table::getInstance()->findBy(
            'name', 'そうだ京都行こう',
            array('order_by' => 'id DESC', 'limit' => 5));

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 5);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest1);
            $this->assertTrue($record->getName() == 'そうだ京都行こう');
        }
        $this->assertTrue($records[0]->getId() == $lastRecord->getId());

        unset($records);
        $records = PackageTest1Table::getInstance()->findBy(
            array('name' => 'そうだ京都行こう'), null,
            array('order_by' => 'id DESC', 'limit' => 5));

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 5);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest1);
            $this->assertTrue($record->getName() == 'そうだ京都行こう');
        }
        $this->assertTrue($records[0]->getId() == $lastRecord->getId());

        unset($records);
        $records = PackageTest1Table::getInstance()->findBy(
            'name = \'そうだ京都行こう\'', null,
            array('order_by' => 'id DESC', 'limit' => 5));

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 5);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest1);
            $this->assertTrue($record->getName() == 'そうだ京都行こう');
        }
        $this->assertTrue($records[0]->getId() == $lastRecord->getId());

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
            throw new IntentionalException('An intentional exception to force rollback');
        } catch (IntentionalException $e) {
            Connection::rollback();
        }
        unset($records);
        $records = PackageTest1Table::getInstance()->findBy();

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 10);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest1);
            $this->assertTrue($record->getName() == 'そうだ京都行こう');
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

        $this->assertTrue(is_array($records));
        $this->assertTrue(empty($records));
    }

    public function testMultiplePrimaryKey()
    {
        /** @var PackageTest2[] $records */
        $records = PackageTest2Table::getInstance()->findBy();
        $this->assertTrue(empty($records));

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

        $this->assertTrue($record instanceof PackageTest2);
        $this->assertTrue($record->isNew());
        $this->assertTrue($user_id === $random_user_id);
        $this->assertTrue($item_id === $random_item_id);
        $this->assertTrue($count === 10);
        $this->assertTrue($created_at === null);
        $this->assertTrue($updated_at === null);

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

        $this->assertTrue($record instanceof PackageTest2);
        $this->assertTrue(!$record->isNew());
        $this->assertTrue(ctype_digit((string)$created_at));
        $this->assertTrue(ctype_digit((string)$updated_at));

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

        $this->assertTrue($record instanceof PackageTest2);
        $this->assertTrue(!$record->isNew());
        $this->assertTrue($user_id_loaded === $user_id);
        $this->assertTrue($item_id_loaded === $item_id);
        $this->assertTrue($count_loaded === $count);
        $this->assertTrue($created_at_loaded === $created_at);
        $this->assertTrue($updated_at_loaded === $updated_at);

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

        $this->assertTrue($record instanceof PackageTest2);
        $this->assertTrue(!$record->isNew());
        $this->assertTrue($user_id_loaded === $user_id);
        $this->assertTrue($item_id_loaded === $item_id);
        $this->assertTrue($count_loaded === $count);
        $this->assertTrue($created_at_loaded === $created_at);
        $this->assertTrue($updated_at_loaded === $updated_at);

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

        $this->assertTrue($record instanceof PackageTest2);
        $this->assertTrue(!$record->isNew());
        $this->assertTrue($user_id_loaded === $user_id);
        $this->assertTrue($item_id_loaded === $item_id);
        $this->assertTrue($count_loaded === $count);
        $this->assertTrue($created_at_loaded === $created_at);
        $this->assertTrue($updated_at_loaded === $updated_at);

        /**
         * Update
         * Sleep 2 seconds before update to ensure updated_at is updated.
         */
        sleep(2);
        $record->setCount($record->getCount() * 2)->save();
        unset($record);
        $record = PackageTest2Table::getInstance()->find($user_id, $item_id);

        $this->assertTrue($record->getCount() === 20);
        $created_at_loaded = $record->getCreatedAt();
        $updated_at_loaded = $record->getUpdatedAt();
        $this->assertTrue($created_at_loaded === $created_at);
        $this->assertTrue($updated_at_loaded !== $updated_at);

        /**
         * Delete
         */
        $record->delete();
        unset($record);
        $record = PackageTest2Table::getInstance()->find($user_id, $item_id);

        $this->assertTrue($record === null);

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

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 10);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        unset($records);
        $records = PackageTest2Table::getInstance()->findByItemId(5);

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 1);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        /**
         * Table::findBy()
         */
        unset($records);
        $records = PackageTest2Table::getInstance()->findBy();

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 10);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        unset($records);
        $records = PackageTest2Table::getInstance()->findBy('user_id', 1);

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 10);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        unset($records);
        $records = PackageTest2Table::getInstance()->findBy('item_id', 5);

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 1);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        unset($records);
        $records = PackageTest2Table::getInstance()->findBy(array('user_id' => 1));

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 10);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        unset($records);
        $records = PackageTest2Table::getInstance()->findBy(array('item_id' => 5));

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 1);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        unset($records);
        $records = PackageTest2Table::getInstance()->findBy('user_id = 1');

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 10);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        unset($records);
        $records = PackageTest2Table::getInstance()->findBy('item_id = 5');

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 1);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }

        unset($records);
        $records = PackageTest2Table::getInstance()->findBy('count > 5');

        $this->assertTrue(is_array($records));
        $this->assertTrue(count($records) == 5);
        foreach ($records as $record) {
            $this->assertTrue($record instanceof PackageTest2);
        }
    }
}
