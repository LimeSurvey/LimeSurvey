<?php

namespace ls\tests\unit\services;

use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\SurveyDeactivate;
use Mockery;

/**
 * Test SurveyDeactivate service
 * @group services
 */
class SurveyDeactivateTest extends TestBaseClass
{
    /**
     * Test that session variables are set correctly when deactivating without confirmation
     */
    public function testSessionVariablesSetOnInitialDeactivation()
    {
        \Yii::app()->session['loginID'] = 1;
        
        // Create a test survey
        $survey = \Survey::model()->insertNewSurvey([
            'language' => 'en',
            'admin' => 'admin'
        ]);
        $surveyId = $survey->sid;
        
        // Activate the survey first
        $this->activateSurvey($surveyId);
        
        // Clear any existing session variables
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        
        // Create service instance
        $surveyModel = \Survey::model()->findByPk($surveyId);
        $permission = \Permission::model();
        $surveyDeactivator = new \SurveyDeactivator();
        $app = \Yii::app();
        $surveyLink = new \SurveyLink();
        $savedControl = \SavedControl::model();
        
        $service = new SurveyDeactivate(
            $surveyModel,
            $permission,
            $surveyDeactivator,
            $app,
            $surveyLink,
            $savedControl
        );
        
        // Call deactivate without 'ok' parameter (first step)
        $result = $service->deactivate($surveyId, []);
        
        // Verify session variables are set
        $this->assertNotEmpty(\Yii::app()->session->get('sNewSurveyTableName'), 'sNewSurveyTableName should be set in session');
        $this->assertNotEmpty(\Yii::app()->session->get('NewSIDDate'), 'NewSIDDate should be set in session');
        
        // Verify the format of session variables
        $tableName = \Yii::app()->session->get('sNewSurveyTableName');
        $this->assertStringContainsString('old_survey_', $tableName, 'Table name should contain old_survey_');
        $this->assertStringContainsString((string)$surveyId, $tableName, 'Table name should contain survey ID');
        
        $sidDate = \Yii::app()->session->get('NewSIDDate');
        $this->assertStringContainsString((string)$surveyId, $sidDate, 'NewSIDDate should contain survey ID');
        $this->assertStringContainsString('_', $sidDate, 'NewSIDDate should contain underscore separator');
        
        // Clean up
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        $survey->delete(true);
    }

    /**
     * Test that existing session variables are removed before setting new ones
     */
    public function testExistingSessionVariablesAreRemoved()
    {
        \Yii::app()->session['loginID'] = 1;
        
        // Set existing session variables
        \Yii::app()->session->add('sNewSurveyTableName', 'old_value_table');
        \Yii::app()->session->add('NewSIDDate', 'old_value_date');
        
        // Create a test survey
        $survey = \Survey::model()->insertNewSurvey([
            'language' => 'en',
            'admin' => 'admin'
        ]);
        $surveyId = $survey->sid;
        
        // Activate the survey first
        $this->activateSurvey($surveyId);
        
        // Create service instance
        $surveyModel = \Survey::model()->findByPk($surveyId);
        $permission = \Permission::model();
        $surveyDeactivator = new \SurveyDeactivator();
        $app = \Yii::app();
        $surveyLink = new \SurveyLink();
        $savedControl = \SavedControl::model();
        
        $service = new SurveyDeactivate(
            $surveyModel,
            $permission,
            $surveyDeactivator,
            $app,
            $surveyLink,
            $savedControl
        );
        
        // Call deactivate without 'ok' parameter
        $result = $service->deactivate($surveyId, []);
        
        // Verify old values are not present
        $tableName = \Yii::app()->session->get('sNewSurveyTableName');
        $sidDate = \Yii::app()->session->get('NewSIDDate');
        
        $this->assertNotEquals('old_value_table', $tableName, 'Old table name should be replaced');
        $this->assertNotEquals('old_value_date', $sidDate, 'Old date should be replaced');
        
        // Verify new values are set
        $this->assertStringContainsString('old_survey_', $tableName, 'New table name should be set');
        $this->assertStringContainsString((string)$surveyId, $sidDate, 'New SID date should be set');
        
        // Clean up
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        $survey->delete(true);
    }

    /**
     * Test that session variables are used during actual deactivation
     */
    public function testSessionVariablesUsedDuringDeactivation()
    {
        $this->markTestSkipped('This test requires database table manipulation which is complex to test in isolation');
    }

    /**
     * Test that NewSIDDate format is correct
     */
    public function testNewSIDDateFormat()
    {
        \Yii::app()->session['loginID'] = 1;
        
        // Create a test survey
        $survey = \Survey::model()->insertNewSurvey([
            'language' => 'en',
            'admin' => 'admin'
        ]);
        $surveyId = $survey->sid;
        
        // Activate the survey first
        $this->activateSurvey($surveyId);
        
        // Clear session variables
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        
        // Create service instance
        $surveyModel = \Survey::model()->findByPk($surveyId);
        $permission = \Permission::model();
        $surveyDeactivator = new \SurveyDeactivator();
        $app = \Yii::app();
        $surveyLink = new \SurveyLink();
        $savedControl = \SavedControl::model();
        
        $service = new SurveyDeactivate(
            $surveyModel,
            $permission,
            $surveyDeactivator,
            $app,
            $surveyLink,
            $savedControl
        );
        
        // Call deactivate
        $result = $service->deactivate($surveyId, []);
        
        $sidDate = \Yii::app()->session->get('NewSIDDate');
        
        // Verify format: {surveyId}_{timestamp}
        $parts = explode('_', $sidDate);
        $this->assertCount(2, $parts, 'NewSIDDate should have two parts separated by underscore');
        $this->assertEquals((string)$surveyId, $parts[0], 'First part should be survey ID');
        $this->assertMatchesRegularExpression('/^\d{14}$/', $parts[1], 'Second part should be 14-digit timestamp (YmdHis format)');
        
        // Clean up
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        $survey->delete(true);
    }

    /**
     * Test that sNewSurveyTableName format is correct
     */
    public function testSNewSurveyTableNameFormat()
    {
        \Yii::app()->session['loginID'] = 1;
        
        // Create a test survey
        $survey = \Survey::model()->insertNewSurvey([
            'language' => 'en',
            'admin' => 'admin'
        ]);
        $surveyId = $survey->sid;
        
        // Activate the survey first
        $this->activateSurvey($surveyId);
        
        // Clear session variables
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        
        // Create service instance
        $surveyModel = \Survey::model()->findByPk($surveyId);
        $permission = \Permission::model();
        $surveyDeactivator = new \SurveyDeactivator();
        $app = \Yii::app();
        $surveyLink = new \SurveyLink();
        $savedControl = \SavedControl::model();
        
        $service = new SurveyDeactivate(
            $surveyModel,
            $permission,
            $surveyDeactivator,
            $app,
            $surveyLink,
            $savedControl
        );
        
        // Call deactivate
        $result = $service->deactivate($surveyId, []);
        
        $tableName = \Yii::app()->session->get('sNewSurveyTableName');
        
        // Verify format: {prefix}old_survey_{surveyId}_{timestamp}
        $prefix = \Yii::app()->db->tablePrefix;
        $this->assertStringStartsWith($prefix . 'old_survey_', $tableName, 'Table name should start with prefix and old_survey_');
        $this->assertStringContainsString('_' . $surveyId . '_', $tableName, 'Table name should contain survey ID with underscores');
        
        // Extract and verify timestamp part
        $expectedStart = $prefix . 'old_survey_' . $surveyId . '_';
        $timestamp = substr($tableName, strlen($expectedStart));
        $this->assertMatchesRegularExpression('/^\d{14}$/', $timestamp, 'Timestamp should be 14 digits (YmdHis format)');
        
        // Clean up
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        $survey->delete(true);
    }

    /**
     * Test that session variables persist between calls
     */
    public function testSessionVariablesPersistBetweenCalls()
    {
        \Yii::app()->session['loginID'] = 1;
        
        // Create a test survey
        $survey = \Survey::model()->insertNewSurvey([
            'language' => 'en',
            'admin' => 'admin'
        ]);
        $surveyId = $survey->sid;
        
        // Activate the survey first
        $this->activateSurvey($surveyId);
        
        // Clear session variables
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        
        // Create service instance
        $surveyModel = \Survey::model()->findByPk($surveyId);
        $permission = \Permission::model();
        $surveyDeactivator = new \SurveyDeactivator();
        $app = \Yii::app();
        $surveyLink = new \SurveyLink();
        $savedControl = \SavedControl::model();
        
        $service = new SurveyDeactivate(
            $surveyModel,
            $permission,
            $surveyDeactivator,
            $app,
            $surveyLink,
            $savedControl
        );
        
        // First call - should set session variables
        $result1 = $service->deactivate($surveyId, []);
        
        $tableName1 = \Yii::app()->session->get('sNewSurveyTableName');
        $sidDate1 = \Yii::app()->session->get('NewSIDDate');
        
        $this->assertNotEmpty($tableName1, 'Table name should be set after first call');
        $this->assertNotEmpty($sidDate1, 'SID date should be set after first call');
        
        // Simulate another call (in practice this might be after page refresh)
        // The session variables should still be available
        $tableName2 = \Yii::app()->session->get('sNewSurveyTableName');
        $sidDate2 = \Yii::app()->session->get('NewSIDDate');
        
        $this->assertEquals($tableName1, $tableName2, 'Table name should persist in session');
        $this->assertEquals($sidDate1, $sidDate2, 'SID date should persist in session');
        
        // Clean up
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        $survey->delete(true);
    }

    /**
     * Test that timestamp in session variables is current
     */
    public function testTimestampInSessionVariablesIsCurrent()
    {
        \Yii::app()->session['loginID'] = 1;
        
        // Create a test survey
        $survey = \Survey::model()->insertNewSurvey([
            'language' => 'en',
            'admin' => 'admin'
        ]);
        $surveyId = $survey->sid;
        
        // Activate the survey first
        $this->activateSurvey($surveyId);
        
        // Clear session variables
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        
        $beforeTime = time();
        
        // Create service instance
        $surveyModel = \Survey::model()->findByPk($surveyId);
        $permission = \Permission::model();
        $surveyDeactivator = new \SurveyDeactivator();
        $app = \Yii::app();
        $surveyLink = new \SurveyLink();
        $savedControl = \SavedControl::model();
        
        $service = new SurveyDeactivate(
            $surveyModel,
            $permission,
            $surveyDeactivator,
            $app,
            $surveyLink,
            $savedControl
        );
        
        // Call deactivate
        $result = $service->deactivate($surveyId, []);
        
        $afterTime = time();
        
        $sidDate = \Yii::app()->session->get('NewSIDDate');
        $parts = explode('_', $sidDate);
        $timestamp = $parts[1];
        
        // Convert timestamp string to time
        $year = substr($timestamp, 0, 4);
        $month = substr($timestamp, 4, 2);
        $day = substr($timestamp, 6, 2);
        $hour = substr($timestamp, 8, 2);
        $minute = substr($timestamp, 10, 2);
        $second = substr($timestamp, 12, 2);
        
        $timestampTime = mktime((int)$hour, (int)$minute, (int)$second, (int)$month, (int)$day, (int)$year);
        
        // Verify timestamp is between before and after time (allowing for small time differences)
        $this->assertGreaterThanOrEqual($beforeTime - 2, $timestampTime, 'Timestamp should be current or recent');
        $this->assertLessThanOrEqual($afterTime + 2, $timestampTime, 'Timestamp should not be in the future');
        
        // Clean up
        \Yii::app()->session->remove('sNewSurveyTableName');
        \Yii::app()->session->remove('NewSIDDate');
        $survey->delete(true);
    }
}