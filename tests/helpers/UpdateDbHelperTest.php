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
        //var_dump($config);

        // Get database name from connection string.
        /*
        $oldConnectionString = $db->connectionString;
        var_dump($db->connectionString);
        $arr = explode(';', $db->connectionString);
        var_dump($arr);
        die;
        $this->assertEquals(4, count($ar));
        $ar = explode('=', $ar[2]);
        $this->assertEquals(2, count($ar));
        $this->assertEquals($ar[0], 'dbname');
         */

        $conStr = \Yii::app()->db->connectionString;
        $isMysql = substr($conStr, 0, 5) === 'mysql';
        $this->assertTrue($isMysql, 'This test only works on MySQL');

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
        $file = __DIR__ . '/../data/sql/create-mysql.153.sql';
        $this->assertFileExists($file);
        $result = $inst->_executeSQLFile($file, 'lime_');
        var_dump($result);
        $result = \db_upgrade_all(153);
        var_dump($result);
        var_dump(\Yii::app()->user->getFlashes());

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);

        try {
            $result = $db->createCommand('DROP DATABASE __test_update_helper')->execute();
            var_dump($result);
            //$this->assertEquals(0, $result, 'Could drop database');
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // This error is OK.
            $this->assertTrue(strpos($msg, 'database doesn\'t exist') !== false);
        }
    }
}
