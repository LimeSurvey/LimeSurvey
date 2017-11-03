<?php

namespace ls\tests;

/**
 * Check the JSON saved in database.
 * @since 2017-11-01
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
    }

    /**
     * 
     */
    public function testCreate()
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
        $this->checkTemplateConfigurationJson($inst->connection);

        // Connect to old database.
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);
    }

    /**
     * 
     */
    public function testUpdateFrom258()
    {
        $connection = self::$testHelper->updateDbFromVersion(258);

        // Check JSON.
        $this->checkMenuEntriesJson($connection);
        $this->checkTemplateConfigurationJson($connection);

        $db = \Yii::app()->getDb();
        $config = require(\Yii::app()->getBasePath() . '/config/config.php');
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);
    }

    /**
     * 
     */
    public function testUpdateFrom315()
    {
        // TODO: Need to fix updatedb_helper to fix broken JSON.
        $this->markTestSkipped();

        $connection = self::$testHelper->updateDbFromVersion(315);

        // Check JSON.
        $this->checkMenuEntriesJson($connection);
        $this->checkTemplateConfigurationJson($connection);

        $db = \Yii::app()->getDb();
        $config = require(\Yii::app()->getBasePath() . '/config/config.php');
        \Yii::app()->setComponent('db', $config['components']['db'], false);
        $db->setActive(true);
    }

    /**
     * @param \CDbConnection $connection
     * @return void
     */
    protected function checkMenuEntriesJson(\CDbConnection $connection)
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

    /**
     * @param \CDbConnection $connection
     * @return void
     */
    protected function checkTemplateConfigurationJson(\CDbConnection $connection)
    {
        $data = $connection->createCommand(
            'SELECT
                files_css,
                files_js,
                files_print_css,
                options,
                cssframework_css,
                cssframework_js ,
                packages_to_load
            FROM
            {{template_configuration}}'
        )->query();
        foreach ($data as $row) {
            foreach ($row as $field => $jsonString) {
                if (!empty($jsonString)) {
                    $json = json_decode($jsonString);
                    $this->assertNotNull($json, $field . ': ' . print_r($row, true));
                } else {
                    // Nothing to check.
                }

            }
        }
    }
}
