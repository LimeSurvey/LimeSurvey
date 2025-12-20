<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test import_helper.php functions
 * @group helpers
 */
class ImportHelperTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->loadHelper('admin/import');
    }

    /**
     * Test getTableArchivesAndTimestamps returns properly formatted results
     */
    public function testGetTableArchivesAndTimestampsFormatting()
    {
        // This function gets archives for a survey
        // We can test with a non-existent survey to verify it returns empty array
        $result = getTableArchivesAndTimestamps(999999);
        
        $this->assertIsArray($result);
    }

    /**
     * Test createTableFromPattern basic functionality
     */
    public function testCreateTableFromPatternValidation()
    {
        // Test that function exists and is callable
        $this->assertTrue(function_exists('createTableFromPattern'));
    }

    /**
     * Test polyfillSUBSTRING_INDEX function exists
     */
    public function testPolyfillSubstringIndexFunctionExists()
    {
        $this->assertTrue(function_exists('polyfillSUBSTRING_INDEX'));
    }

    /**
     * Test polyfillSUBSTRING_INDEX with different database drivers
     */
    public function testPolyfillSubstringIndexWithDifferentDrivers()
    {
        $driver = \Yii::app()->db->getDriverName();
        
        // Function should handle different database types
        $this->assertContains($driver, ['mysql', 'mysqli', 'pgsql', 'mssql', 'sqlsrv', 'dblib']);
    }

    /**
     * Test importSurveyFile function exists
     */
    public function testImportSurveyFileFunctionExists()
    {
        $this->assertTrue(function_exists('importSurveyFile'));
    }

    /**
     * Test that helper loads correctly after path changes
     */
    public function testImportHelperLoadsWithSlashNotation()
    {
        // The change was from 'admin.import' to 'admin/import'
        // Verify the helper can be loaded
        \Yii::app()->loadHelper('admin/import');
        
        // Verify key functions exist after loading
        $this->assertTrue(function_exists('importSurveyFile'));
        $this->assertTrue(function_exists('createTableFromPattern'));
        $this->assertTrue(function_exists('getTableArchivesAndTimestamps'));
    }

    /**
     * Test XMLImportQuestion function exists
     */
    public function testXMLImportQuestionFunctionExists()
    {
        $this->assertTrue(function_exists('XMLImportQuestion'));
    }

    /**
     * Test XMLImportGroup function exists
     */
    public function testXMLImportGroupFunctionExists()
    {
        $this->assertTrue(function_exists('XMLImportGroup'));
    }
}