<?php

namespace ls\tests;

/**
 * The settings that decide the structure of the response tables must not follow a change of the
 * survey group settings after the activation.
 */
class SurveyInheritedActivationSettingsTest extends TestBaseClass
{
    /** @var array Original settings of survey group 1 */
    private static $groupSettings;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_143933.lss');
        $groupSettings = \SurveysGroupsettings::model()->findByPk(1);
        self::$groupSettings = $groupSettings->getAttributes(['savetimings', 'datestamp', 'ipaddr']);
        \Survey::model()->updateByPk(self::$surveyId, ['gsid' => 1, 'savetimings' => 'Y', 'datestamp' => 'I', 'ipaddr' => 'I']);
        self::setGroupSettings(['savetimings' => 'N', 'datestamp' => 'Y', 'ipaddr' => 'Y']);
    }

    public static function tearDownAfterClass(): void
    {
        self::setGroupSettings(self::$groupSettings);
        parent::tearDownAfterClass();
    }

    /**
     * Saves settings of survey group 1, this also clears the cached group settings
     *
     * @param array $settings
     * @return void
     */
    private static function setGroupSettings(array $settings)
    {
        $groupSettings = \SurveysGroupsettings::model()->findByPk(1);
        $groupSettings->setAttributes($settings, false);
        $groupSettings->save(false);
        \Survey::model()->resetCache();
    }

    /**
     * The activation stores the values the tables were created with instead of "inherit".
     *
     * @return void
     */
    public function testActivationStoresInheritedSettings()
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $result = (new \SurveyActivator($survey))->activate();
        $this->assertSame('OK', $result['status'] ?? null);
        // The cached table names do not include the tables created by the activation yet
        \Yii::app()->db->schema->refresh();

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $columns = \Yii::app()->db->schema->getTable($survey->responsesTableName)->getColumnNames();
        $this->assertSame('Y', $survey->savetimings);
        $this->assertTrue($survey->hasTimingsTable);
        $this->assertSame(in_array('datestamp', $columns) ? 'Y' : 'N', $survey->datestamp);
        $this->assertSame(in_array('ipaddr', $columns) ? 'Y' : 'N', $survey->ipaddr);
    }

    /**
     * An active survey that still stores "inherit" uses the values its tables were created with.
     *
     * @depends testActivationStoresInheritedSettings
     * @return void
     */
    public function testActiveSurveyIgnoresChangedGroupSettings()
    {
        // Like a survey activated by an older version, the group settings differ from the tables
        \Survey::model()->updateByPk(self::$surveyId, ['savetimings' => 'I', 'datestamp' => 'I', 'ipaddr' => 'I']);
        \Survey::model()->resetCache();
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $columns = \Yii::app()->db->schema->getTable($survey->responsesTableName)->getColumnNames();
        $this->assertNotContains('datestamp', $columns);
        $this->assertNotContains('ipaddr', $columns);

        $this->assertTrue($survey->isSaveTimings);
        $this->assertFalse($survey->isDateStamp);
        $this->assertFalse($survey->isIpAddr);
        $surveyInfo = getSurveyInfo(self::$surveyId, '', true);
        $this->assertSame('Y', $surveyInfo['savetimings']);
        $this->assertSame('N', $surveyInfo['datestamp']);
    }
}
