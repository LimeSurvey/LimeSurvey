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
        self::$testHelper->teardownDatabase('__test_update_helper_258');
        self::$testHelper->teardownDatabase('__test_update_helper_315');
        self::$testHelper->teardownDatabase('__test_install_script');

        $dbo = \Yii::app()->getDb();
        $dbo->setActive(false);
        $config = require(\Yii::app()->getBasePath() . '/config/config.php');
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $dbo->setActive(true);
    }

    /**
     * Run the database PHP install script.
     * @group install
     */
    public function testInstallPhp()
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

        $db = \Yii::app()->getDb();
        $config = require(\Yii::app()->getBasePath() . '/config/config.php');

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);
    }
}
