<?php

namespace ls\tests;

/**
 * The settings that can't be changed while the survey is active must not follow a change of the
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
        self::$groupSettings = $groupSettings->getAttributes(['savetimings', 'datestamp', 'ipaddr', 'ipanonymize']);
        \Survey::model()->updateByPk(self::$surveyId, ['gsid' => 1, 'savetimings' => 'I', 'datestamp' => 'I', 'ipaddr' => 'I', 'ipanonymize' => 'I']);
        self::setGroupSettings(['savetimings' => 'Y', 'datestamp' => 'Y', 'ipaddr' => 'N', 'ipanonymize' => 'N']);
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
     * The activation creates the tables with the inherited values and stores them instead of "inherit".
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
        $this->assertSame('Y', $survey->datestamp);
        $this->assertContains('datestamp', $columns);
        $this->assertSame('N', $survey->ipaddr);
        $this->assertNotContains('ipaddr', $columns);
        $this->assertSame('N', $survey->ipanonymize);
    }

    /**
     * An active survey that still stores "inherit" stores the values its tables were created with once it is loaded.
     *
     * @depends testActivationStoresInheritedSettings
     * @return void
     */
    public function testActiveSurveyIgnoresChangedGroupSettings()
    {
        // Like a survey activated by an older version, the group settings differ from the tables
        \Survey::model()->updateByPk(self::$surveyId, ['savetimings' => 'I', 'datestamp' => 'I', 'ipaddr' => 'I', 'ipanonymize' => 'I']);
        self::setGroupSettings(['savetimings' => 'N', 'datestamp' => 'N', 'ipaddr' => 'Y', 'ipanonymize' => 'Y']);

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertTrue($survey->isSaveTimings);
        $this->assertTrue($survey->isDateStamp);
        $this->assertFalse($survey->isIpAddr);
        $this->assertTrue($survey->isIpAnonymize);
        $fieldMap = createFieldMap($survey, 'short', true, false, $survey->language);
        $this->assertArrayHasKey('datestamp', $fieldMap);
        $this->assertArrayNotHasKey('ipaddr', $fieldMap);
        $surveyInfo = getSurveyInfo(self::$surveyId, '', true);
        $this->assertSame('Y', $surveyInfo['savetimings']);
        $this->assertSame('Y', $surveyInfo['datestamp']);

        $storedSettings = \Yii::app()->db->createCommand()
            ->select('savetimings, datestamp, ipaddr, ipanonymize')
            ->from('{{surveys}}')
            ->where('sid = :sid', [':sid' => self::$surveyId])
            ->queryRow();
        $this->assertSame(['savetimings' => 'Y', 'datestamp' => 'Y', 'ipaddr' => 'N', 'ipanonymize' => 'Y'], $storedSettings);
    }
}
