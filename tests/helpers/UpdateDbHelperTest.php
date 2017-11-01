<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * @since 2017-06-16
 * @group dbhelper
 * @group db
 */
class UpdateDbHelperTest extends TestBaseClass
{
    /**
     * Tear down fixtures.
     */
    public static function teardownAfterClass()
    {
        $dbo = \Yii::app()->getDb();
        try {
            $dbo->createCommand('DROP DATABASE __test_update_helper_258')->execute();
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // Only this error is OK.
            self::assertTrue(strpos($msg, 'database doesn\'t exist') !== false);
        }

        try {
            $dbo->createCommand('DROP DATABASE __test_update_helper_315')->execute();
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // Only this error is OK.
            self::assertTrue(strpos($msg, 'database doesn\'t exist') !== false);
        }

        try {
            $dbo->createCommand('DROP DATABASE __test_install_script')->execute();
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // Only this error is OK.
            self::assertTrue(strpos($msg, 'database doesn\'t exist') !== false);
        }

        $dbo->setActive(false);
        unset($dbo);
        $config = require(\Yii::app()->getBasePath() . '/config/config.php');
        \Yii::app()->setComponent('db', $config['components']['db'], false);
    }

    /**
     * Test the SQL install script.
     * Not used.
     */
    public function testInstallSql()
    {
        // SQL not used anymore, see the PHP file.
        $this->markTestSkipped();

        $db = \Yii::app()->getDb();

        $config = require(\Yii::app()->getBasePath() . '/config/config.php');
        $result = self::$testHelper->connectToNewDatabase('__test_install_script');
        $this->assertTrue($result, 'Could connect to new database');

        // Get InstallerController.
        $inst = new \InstallerController('foobar');
        $inst->connection = \Yii::app()->db;

        // Check SQL file.
        $file = \Yii::app()->basePath . '/../installer/sql/create-mysql.sql';
        $this->assertFileExists($file);

        // Run SQL install file.
        $result = $inst->_executeSQLFile($file, 'lime_');
        if ($result) {
            print_r($result);
        }
        $this->assertEquals([], $result, 'No error messages from _executeSQLFile');

        // Dump database to file.
        /*
        $output = array();
        $result = exec(
            sprintf(
                'mysqldump -u %s -p%s __test_install_script > tests/data/tmp/__test_install_script-dump.sql',
                $config['components']['db']['username'],
                $config['components']['db']['password']
            ),
            $output
        );
        $this->assertEmpty($output, 'No output from mysqldump');
        $this->assertEmpty($result, 'No last line output from mysqldump');
         */

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);
    }

    /**
     * Run the database PHP install script.
     * @group install
     */
    public function testInstallPHP()
    {
        $db = \Yii::app()->getDb();

        $config = require(\Yii::app()->getBasePath() . '/config/config.php');
        $result = self::$testHelper->connectToNewDatabase('__test_install_script');
        $this->assertTrue($result, 'Could connect to new database');

        // Get InstallerController.
        $inst = new \InstallerController('foobar');
        $inst->connection = \Yii::app()->db;
        $filename = dirname(APPPATH).'/installer/create-database.php';
        $result = $inst->_setup_tables($filename);
        if ($result) {
            print_r($result);
        }

        // Dump database to file.
        /*
        $output = array();
        $result = exec(
            sprintf(
                'mysqldump -u %s -p%s __test_install_script > tests/data/tmp/__test_install_script-dump.sql',
                $config['components']['db']['username'],
                $config['components']['db']['password']
            ),
            $output
        );
        $this->assertEmpty($output, 'No output from mysqldump');
        $this->assertEmpty($result, 'No last line output from mysqldump');
         */

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);
    }

    /**
     * Run db_upgrade_all() from dbversion 258, to make sure
     * there are no conflicts or syntax errors.
     * @group upgradeall
     */
    public function testDbUpgradeFrom258()
    {
        self::$testHelper->updateDbFromVersion(258);

        $db = \Yii::app()->getDb();
        $config = require(\Yii::app()->getBasePath() . '/config/config.php');

        // Dump database to file.
        /*
        $output = array();
        $result = exec(
            sprintf(
                'mysqldump -u %s -p%s __test_update_helper_258 > tests/data/tmp/__test_update_helper_258-dump.sql',
                $config['components']['db']['username'],
                $config['components']['db']['password']
            ),
            $output
        );
        $this->assertEmpty($output, 'No output from mysqldump');
        $this->assertEmpty($result, 'No last line output from mysqldump');
         */

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);

        // Database is deleted in teardownAfterClass().
    }

    /**
     * @group from315
     */
    public function testDbUpgradeFrom315()
    {
        self::$testHelper->updateDbFromVersion(315);

    }
}
