<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test that helpers load correctly with new path notation
 * This tests the change from dot notation (admin.import) to slash notation (admin/import)
 * @group helpers
 */
class HelperLoadingTest extends TestBaseClass
{
    /**
     * Test loading admin/import helper with slash notation
     */
    public function testLoadImportHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/import');
        
        $this->assertTrue(function_exists('importSurveyFile'));
        $this->assertTrue(function_exists('XMLImportQuestion'));
        $this->assertTrue(function_exists('XMLImportGroup'));
        $this->assertTrue(function_exists('createTableFromPattern'));
    }

    /**
     * Test loading admin/activate helper with slash notation
     */
    public function testLoadActivateHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/activate');
        
        $this->assertTrue(function_exists('checkQuestions'));
        $this->assertTrue(function_exists('checkGroup'));
        $this->assertTrue(function_exists('fixNumbering'));
    }

    /**
     * Test loading admin/statistics helper with slash notation
     */
    public function testLoadStatisticsHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/statistics');
        
        $this->assertTrue(class_exists('statistics_helper'));
    }

    /**
     * Test loading admin/htmleditor helper with slash notation
     */
    public function testLoadHtmlEditorHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/htmleditor');
        
        $this->assertTrue(function_exists('prepareEditorScript'));
    }

    /**
     * Test loading admin/exportresults helper with slash notation
     */
    public function testLoadExportResultsHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/exportresults');
        
        $this->assertTrue(function_exists('exportSurvey'));
    }

    /**
     * Test loading admin/label helper with slash notation
     */
    public function testLoadLabelHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/label');
        
        $this->assertTrue(function_exists('updateset'));
        $this->assertTrue(function_exists('insertlabelset'));
    }

    /**
     * Test loading admin/template helper with slash notation
     */
    public function testLoadTemplateHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/template');
        
        $this->assertTrue(function_exists('templatezip'));
        $this->assertTrue(function_exists('getTemplatePath'));
    }

    /**
     * Test loading admin/token helper with slash notation
     */
    public function testLoadTokenHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/token');
        
        $this->assertTrue(function_exists('createTokenTable'));
    }

    /**
     * Test loading admin/backupdb helper with slash notation
     */
    public function testLoadBackupDbHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('admin/backupdb');
        
        $this->assertTrue(function_exists('getDatabaseSize'));
        $this->assertTrue(function_exists('_getDbName'));
    }

    /**
     * Test loading update/update helper with slash notation
     */
    public function testLoadUpdateHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('update/update');
        
        $this->assertTrue(function_exists('CheckForDBUpgrades'));
    }

    /**
     * Test loading update/updatedb helper with slash notation
     */
    public function testLoadUpdateDbHelperWithSlashNotation()
    {
        \Yii::app()->loadHelper('update/updatedb');
        
        $this->assertTrue(function_exists('db_upgrade_all'));
    }

    /**
     * Test that common helper still loads
     */
    public function testLoadCommonHelper()
    {
        \Yii::app()->loadHelper('common');
        
        $this->assertTrue(function_exists('sanitize_int'));
        $this->assertTrue(function_exists('sanitize_paranoid_string'));
    }

    /**
     * Test that sanitize helper loads
     */
    public function testLoadSanitizeHelper()
    {
        \Yii::app()->loadHelper('sanitize');
        
        $this->assertTrue(function_exists('sanitize_languagecode'));
        $this->assertTrue(function_exists('sanitize_languagecodeS'));
        $this->assertTrue(function_exists('sanitize_filename'));
    }

    /**
     * Test multiple helper loads don't cause issues
     */
    public function testMultipleHelperLoads()
    {
        // Load same helper multiple times
        \Yii::app()->loadHelper('admin/import');
        \Yii::app()->loadHelper('admin/import');
        \Yii::app()->loadHelper('admin/import');
        
        $this->assertTrue(function_exists('importSurveyFile'));
    }

    /**
     * Test loading multiple different helpers
     */
    public function testLoadingMultipleDifferentHelpers()
    {
        \Yii::app()->loadHelper('admin/import');
        \Yii::app()->loadHelper('admin/activate');
        \Yii::app()->loadHelper('admin/statistics');
        
        $this->assertTrue(function_exists('importSurveyFile'));
        $this->assertTrue(function_exists('checkQuestions'));
        $this->assertTrue(class_exists('statistics_helper'));
    }
}