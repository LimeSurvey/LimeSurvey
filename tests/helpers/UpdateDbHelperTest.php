<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * @since 2017-06-16
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
            $dbo->createCommand('DROP DATABASE __test_update_helper')->execute();
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
    }

    /**
     * Test the SQL install script.
     */
    public function testInstallSql()
    {
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
        $this->assertEquals([], $result, 'No error messages from _executeSQLFile');

        // Dump database to file.
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

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);
    }

    /**
     * Run db_upgrade_all() from dbversion 153, to make sure
     * there are no conflicts or syntax errors.
     * @group upgradeall
     */
    public function testDbUpgradeAll()
    {
        $db = \Yii::app()->getDb();

        $config = require(\Yii::app()->getBasePath() . '/config/config.php');
        $result = self::$testHelper->connectToNewDatabase('__test_update_helper');
        $this->assertTrue($result, 'Could connect to new database');

        // Get InstallerController.
        $inst = new \InstallerController('foobar');
        $inst->connection = \Yii::app()->db;

        // Check SQL file.
        $file = __DIR__ . '/../data/sql/create-mysql.153.sql';
        $this->assertFileExists($file);

        // Run SQL install file.
        $result = $inst->_executeSQLFile($file, 'lime_');
        $this->assertEquals([], $result, 'No error messages from _executeSQLFile');

        // Run upgrade.
        $result = \db_upgrade_all(153);
        $this->assertTrue($result, 'Upgrade successful');

        // Check error messages.
        $flashes = \Yii::app()->user->getFlashes();
        $this->assertEmpty($flashes, 'No flash error messages');

        // Dump database to file.
        $output = array();
        $result = exec(
            sprintf(
                'mysqldump -u %s -p%s __test_update_helper > tests/data/tmp/__test_update_helper-dump.sql',
                $config['components']['db']['username'],
                $config['components']['db']['password']
            ),
            $output
        );
        $this->assertEmpty($output, 'No output from mysqldump');
        $this->assertEmpty($result, 'No last line output from mysqldump');

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);

        // Database is deleted in teardownAfterClass().
    }
}
