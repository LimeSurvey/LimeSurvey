<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use LimeExpressionManager;
use Token;
use Yii;

/**
 * Tests that the survey logic file accepts all participant table columns as TOKEN: variables.
 */
class SurveyLogicFileParticipantTableColumnsTest extends TestBaseClass
{
    /**
     * Imports the survey (Q00 depends on TOKEN:PARTICIPANT_ID, TOKEN:TID, ...) and creates its participant table.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_15652_participant_table_columns.lss');
        Token::createTable(self::$surveyId);
        Yii::app()->db->schema->refresh();
    }

    /**
     * Participant table columns that are not in getTokenFieldsAndNames() are not reported as errors.
     */
    public function testParticipantTableColumnsAreNotReportedAsErrors()
    {
        SetSurveyLanguage(self::$surveyId, '');
        killSurveySession(self::$surveyId);

        $result = LimeExpressionManager::ShowSurveyLogicFile(
            self::$surveyId,
            null,
            null,
            LEM_DEBUG_VALIDATION_SUMMARY + LEM_DEBUG_VALIDATION_DETAIL + LEM_PRETTY_PRINT_ALL_SYNTAX
        );

        $this->assertEmpty($result['errors']);
        $this->assertStringNotContainsString('em-var-error', $result['html']);
        $this->assertStringContainsString('TOKEN:PARTICIPANT_ID', $result['html']);
    }
}
