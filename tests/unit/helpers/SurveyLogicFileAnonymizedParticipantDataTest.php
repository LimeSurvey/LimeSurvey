<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use LimeExpressionManager;
use Survey;
use Token;
use Yii;

/**
 * Tests that the survey logic file warns about participant data used in an anonymized survey.
 */
class SurveyLogicFileAnonymizedParticipantDataTest extends TestBaseClass
{
    /**
     * Imports the anonymized survey (Q00 depends on TOKEN:FIRSTNAME) and creates its participant table.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_14719_anonymized_participant_data.lss');
        Token::createTable(self::$surveyId);
        Yii::app()->db->schema->refresh();
    }

    /**
     * Participant data in an anonymized survey gets a warning, not an error.
     */
    public function testParticipantDataInAnonymizedSurveyIsReportedAsWarning()
    {
        $result = $this->getSurveyLogicFile();

        $this->assertEmpty($result['errors']);
        $this->assertStringContainsString('1 question contains warnings that need to be verified.', $result['html']);
        $this->assertStringContainsString('This survey is anonymized, so participant data is always empty.', $result['html']);
    }

    /**
     * Participant data in a survey that is not anonymized gets no warning.
     */
    public function testParticipantDataInNotAnonymizedSurveyIsNotReported()
    {
        $survey = Survey::model()->findByPk(self::$surveyId);
        $survey->anonymized = 'N';
        $this->assertTrue($survey->save());

        $result = $this->getSurveyLogicFile();

        $this->assertEmpty($result['errors']);
        $this->assertStringNotContainsString('This survey is anonymized, so participant data is always empty.', $result['html']);
    }

    /**
     * Returns the survey logic file of the test survey.
     * @return array
     */
    private function getSurveyLogicFile()
    {
        SetSurveyLanguage(self::$surveyId, '');
        killSurveySession(self::$surveyId);

        return LimeExpressionManager::ShowSurveyLogicFile(
            self::$surveyId,
            null,
            null,
            LEM_DEBUG_VALIDATION_SUMMARY + LEM_DEBUG_VALIDATION_DETAIL + LEM_PRETTY_PRINT_ALL_SYNTAX
        );
    }
}
