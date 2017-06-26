<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * @since 2017-06-16
 */
class UpdateDbHelperTest extends TestBaseClass
{
    /**
     * Run db_upgrade_all().
     */
    public function testBasic()
    {
        $db = \Yii::app()->getDb();

        $config = require(\Yii::app()->getBasePath() . '/config/config.php');

        // Check that we're using MySQL.
        $conStr = \Yii::app()->db->connectionString;
        $isMysql = substr($conStr, 0, 5) === 'mysql';
        if (!$isMysql) {
            $this->markTestSkipped('Only works on MySQL');
            return;
        }
        $this->assertTrue($isMysql, 'This test only works on MySQL');

        // Get database name.
        preg_match("/dbname=([^;]*)/", \Yii::app()->db->connectionString, $matches);
        $this->assertEquals(2, count($matches));
        $oldDatabase = $matches[1];

        try {
            $result = $db->createCommand('CREATE DATABASE __test_update_helper DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')->execute();
            $this->assertEquals(1, $result, 'Could create database');
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // This error is OK.
            $this->assertTrue(strpos($msg, 'database exists') !== false);
        }

        // Connect to new database.
        $db->setActive(false);
        $newConfig = $config;
        $newConfig['components']['db']['connectionString'] = str_replace(
            'dbname=' . $oldDatabase,
            'dbname=__test_update_helper',
            $config['components']['db']['connectionString']
        );
        \Yii::app()->setComponent('db', $newConfig['components']['db'], false);

        // Run everything
        $inst = new \InstallerController('foobar');
        $inst->connection = \Yii::app()->db;

        // Check SQL file.
        $file = __DIR__ . '/../data/sql/create-mysql.153.sql';
        $this->assertFileExists($file);

        // Run SQL install file.
        $result = $inst->_executeSQLFile($file, 'lime_');
        $this->assertEquals([], $result, 'No error messages');

        // Run upgrade.
        $result = \db_upgrade_all(153);
        $this->assertTrue($result, 'Upgrade successful');

        // Check error messages.
        $flashes = \Yii::app()->user->getFlashes();
        $this->assertEmpty($flashes, 'No flash error messages');

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);

        try {
            $result = $db->createCommand('DROP DATABASE __test_update_helper')->execute();
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // Only this error is OK.
            $this->assertTrue(strpos($msg, 'database doesn\'t exist') !== false);
        }
    }
}
