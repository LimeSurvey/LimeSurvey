<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyDeactivate;

/**
 * Activating and deactivating must change the survey's active state even if
 * another survey setting does not pass validation.
 */
class SurveyActivationInvalidSettingTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_143933.lss');
        // Store an invalid admin email directly, bypassing validation, like an old import could
        \Survey::model()->updateByPk(self::$surveyId, ['adminemail' => 'not-an-email']);
        \Survey::model()->resetCache();
    }

    /**
     * Activation marks the survey as active.
     *
     * @return void
     */
    public function testActivateWithInvalidAdminEmail()
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertFalse($survey->validate(), 'The test survey must have an invalid setting.');

        $activator = new \SurveyActivator($survey);
        $result = $activator->activate();

        $this->assertSame('OK', $result['status'] ?? null);
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertSame('Y', $survey->active);
    }

    /**
     * Deactivation archives the response table and marks the survey as inactive.
     *
     * @depends testActivateWithInvalidAdminEmail
     * @return void
     */
    public function testDeactivateWithInvalidAdminEmail()
    {
        // The cached table names do not include the response table created by the activation yet
        \Yii::app()->db->schema->refresh();
        \Yii::app()->db->createCommand()->insert('{{responses_' . self::$surveyId . '}}', ['startlanguage' => 'en']);
        $lastId = (int) \Yii::app()->db->createCommand()
            ->select('MAX(id)')
            ->from('{{responses_' . self::$surveyId . '}}')
            ->queryScalar();

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $surveyDeactivate = new SurveyDeactivate(
            $survey,
            \Permission::model(),
            new \SurveyDeactivator($survey),
            App(),
            \SurveyLink::model(),
            \SavedControl::model()
        );
        $surveyDeactivate->setArchivedResponseSettings(\ArchivedTableSettings::model());
        $surveyDeactivate->setArchivedTimingsSettings(\ArchivedTableSettings::model());
        $surveyDeactivate->setArchivedTokenSettings(\ArchivedTableSettings::model());
        $surveyDeactivate->deactivate(self::$surveyId, ['ok' => true], true);

        $this->assertFalse(tableExists('responses_' . self::$surveyId));
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $this->assertSame('N', $survey->active);
        $this->assertSame($lastId + 1, (int) $survey->autonumber_start);
    }
}
