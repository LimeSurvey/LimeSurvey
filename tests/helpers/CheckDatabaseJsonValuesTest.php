<?php

namespace ls\tests;

/**
 * Test expression evaluation in PHP vs JS.
 * @since 2017-06-16
 * @group json
 */
class CheckDatabaseJsonValuesTest extends TestBaseClass
{
    /**
     * Tear down fixtures.
     */
    public static function teardownAfterClass()
    {
        $dbo = \Yii::app()->getDb();
        try {
            $dbo->createCommand('DROP DATABASE __test_check_database_json')->execute();
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // Only this error is OK.
            self::assertTrue(strpos($msg, 'database doesn\'t exist') !== false);
        }
    }

    /**
     * 
     */
    public function testBasic()
    {
        $db = \Yii::app()->getDb();

        $config = require(\Yii::app()->getBasePath() . '/config/config.php');
        $result = self::$testHelper->connectToNewDatabase('__test_check_database_json');
        $this->assertTrue($result, 'Could connect to new database');

        // Get InstallerController.
        $inst = new \InstallerController('foobar');
        $inst->connection = \Yii::app()->db;
        $filename = dirname(APPPATH).'/installer/create-database.php';
        $result = $inst->_setup_tables($filename);
        if ($result) {
            print_r($result);
        }

        // Check JSON.
        $this->checkMenuEntriesJson($inst->connection);

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);
    }

    /**
     * 
     */
    protected function checkMenuEntriesJson($connection)
    {
        $data = $connection->createCommand('SELECT data from {{surveymenu_entries}}')->query();
        foreach ($data as $row) {
            $jsonString = $row['data'];
            if (!empty($jsonString)) {
                $json = json_decode($jsonString);
                $this->assertNotNull($json, print_r($row, true));
            } else {
                // Nothing to check.
            }
        }
    }
}
