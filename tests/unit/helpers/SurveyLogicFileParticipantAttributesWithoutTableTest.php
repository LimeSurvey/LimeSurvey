<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use LimeExpressionManager;
use Yii;

/**
 * Tests that the survey logic file accepts described participant attributes as TOKEN: variables
 * when the survey has no participant table.
 */
class SurveyLogicFileParticipantAttributesWithoutTableTest extends TestBaseClass
{
    /**
     * Imports the survey (Q00 uses TOKEN:FIRSTNAME, TOKEN:ATTRIBUTE_1 and TOKEN:ATTRIBUTE_10, which are described
     * in its participant attribute settings) without creating its participant table.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_20195_participant_attributes_without_table.lss');
    }

    /**
     * Described participant attributes are not reported as errors, like the basic participant fields.
     */
    public function testDescribedAttributesAreNotReportedAsErrors()
    {
        $this->assertFalse(self::$testSurvey->hasTokensTable);

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
        $this->assertStringContainsString('TOKEN:ATTRIBUTE_1', $result['html']);
    }
}
