<?php

namespace ls\tests\unit\services;

use ls\tests\TestBaseClass;
use SurveyDeactivate;
use Survey;
use Mockery;

/**
 * Test SurveyDeactivate service
 * @group services
 */
class SurveyDeactivateTest extends TestBaseClass
{
    /**
     * Test that session variables are properly set during deactivation
     */
    public function testDeactivateSetsSess ionVariables()
    {
        \Yii::app()->session['loginID'] = 1;
        
        // Create a test survey
        $survey = Survey::model()->insertNewSurvey(array(
            'language' => 'en',
            'admin' => 'testadmin'
        ));
        
        $surveyId = $survey->sid;
        
        // Activate the survey first
        \Yii::app()->loadHelper('admin/activate');
        $activator = new \SurveyActivator($survey);
        $result = $activator->activate();
        
        $this->assertTrue($result['result'], 'Survey should be activated successfully');
        
        // Now test deactivation
        $date = date('YmdHis', time());
        
        // Clear any existing session variables
        if (\Yii::app()->session->get('sNewSurveyTableName')) {
            \Yii::app()->session->remove('sNewSurveyTableName');
        }
        if (\Yii::app()->session->get('NewSIDDate')) {
            \Yii::app()->session->remove('NewSIDDate');
        }
        
        // Call deactivate with no params (should show confirmation)
        $deactivator = new SurveyDeactivate();
        $result = $deactivator->deactivate($surveyId, $date);
        
        // Check that session variables are set
        $this->assertNotEmpty(\Yii::app()->session->get('sNewSurveyTableName'));
        $this->assertNotEmpty(\Yii::app()->session->get('NewSIDDate'));
        
        // Verify format of session variables
        $tableName = \Yii::app()->session->get('sNewSurveyTableName');
        $this->assertStringContainsString("old_survey_{$surveyId}_", $tableName);
        
        $sidDate = \Yii::app()->session->get('NewSIDDate');
        $this->assertStringContainsString((string)$surveyId, $sidDate);
        $this->assertStringContainsString('_', $sidDate);
        
        // Clean up
        $survey->delete();
    }

    /**
     * Test that session variables are used consistently
     */
    public function testSessionVariablesConsistency()
    {
        \Yii::app()->session['loginID'] = 1;
        
        $survey = Survey::model()->insertNewSurvey(array(
            'language' => 'en',
            'admin' => 'testadmin'
        ));
        
        $surveyId = $survey->sid;
        
        // Activate survey
        \Yii::app()->loadHelper('admin/activate');
        $activator = new \SurveyActivator($survey);
        $activator->activate();
        
        $date = date('YmdHis', time());
        
        // Set session variables manually
        $expectedTableName = \Yii::app()->db->tablePrefix . "old_survey_{$surveyId}_{$date}";
        $expectedSidDate = "{$surveyId}_{$date}";
        
        \Yii::app()->session->add('sNewSurveyTableName', $expectedTableName);
        \Yii::app()->session->add('NewSIDDate', $expectedSidDate);
        
        // Retrieve and verify
        $this->assertSame($expectedTableName, \Yii::app()->session->get('sNewSurveyTableName'));
        $this->assertSame($expectedSidDate, \Yii::app()->session->get('NewSIDDate'));
        
        // Clean up
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        $survey->delete();
    }

    /**
     * Test that existing session variables are removed before setting new ones
     */
    public function testExistingSessionVariablesAreRemoved()
    {
        \Yii::app()->session['loginID'] = 1;
        
        // Set some old session variables
        \Yii::app()->session->add('sNewSurveyTableName', 'old_table_name');
        \Yii::app()->session->add('NewSIDDate', 'old_date');
        
        $survey = Survey::model()->insertNewSurvey(array(
            'language' => 'en',
            'admin' => 'testadmin'
        ));
        
        $surveyId = $survey->sid;
        
        // Activate survey
        \Yii::app()->loadHelper('admin/activate');
        $activator = new \SurveyActivator($survey);
        $activator->activate();
        
        $date = date('YmdHis', time());
        
        // Call deactivate
        $deactivator = new SurveyDeactivate();
        $result = $deactivator->deactivate($surveyId, $date);
        
        // Verify old values are replaced
        $newTableName = \Yii::app()->session->get('sNewSurveyTableName');
        $newSidDate = \Yii::app()->session->get('NewSIDDate');
        
        $this->assertNotSame('old_table_name', $newTableName);
        $this->assertNotSame('old_date', $newSidDate);
        $this->assertStringContainsString((string)$surveyId, $newTableName);
        $this->assertStringContainsString((string)$surveyId, $newSidDate);
        
        // Clean up
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        $survey->delete();
    }

    /**
     * Test deactivation date format
     */
    public function testDeactivationDateFormat()
    {
        $date = date('YmdHis', time());
        
        // Verify date format
        $this->assertMatchesRegularExpression('/^\d{14}$/', $date, 'Date should be in YmdHis format (14 digits)');
        
        // Test parsing
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        $hour = substr($date, 8, 2);
        $minute = substr($date, 10, 2);
        $second = substr($date, 12, 2);
        
        $this->assertGreaterThanOrEqual(2020, (int)$year);
        $this->assertLessThanOrEqual(12, (int)$month);
        $this->assertGreaterThan(0, (int)$month);
        $this->assertLessThanOrEqual(31, (int)$day);
        $this->assertGreaterThan(0, (int)$day);
        $this->assertLessThan(24, (int)$hour);
        $this->assertLessThan(60, (int)$minute);
        $this->assertLessThan(60, (int)$second);
    }

    /**
     * Test session cleanup
     */
    public function testSessionCleanup()
    {
        // Set test session variables
        \Yii::app()->session->add('sNewSurveyTableName', 'test_table');
        \Yii::app()->session->add('NewSIDDate', 'test_date');
        
        // Verify they exist
        $this->assertNotEmpty(\Yii::app()->session->get('sNewSurveyTableName'));
        $this->assertNotEmpty(\Yii::app()->session->get('NewSIDDate'));
        
        // Remove them
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        
        // Verify they're gone
        $this->assertEmpty(\Yii::app()->session->get('sNewSurveyTableName'));
        $this->assertEmpty(\Yii::app()->session->get('NewSIDDate'));
    }
}