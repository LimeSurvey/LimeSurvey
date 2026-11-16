<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use LimeExpressionManager;
use Yii;

/**
 * Tests that the survey logic file warns about conditions using a later question.
 */
class SurveyLogicFileLaterQuestionTest extends TestBaseClass
{
    /**
     * Imports the survey: Q00 depends on the later Yes2, Yes2 depends on the earlier Yes1.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_14467_later_question_relevance.lss');
    }

    /**
     * Only the condition that uses a later question gets a warning.
     */
    public function testConditionUsingLaterQuestionIsReportedAsWarning()
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
        $this->assertStringContainsString('1 question contains warnings that need to be verified.', $result['html']);
        // Yes1 and Yes2 come after Q00 and only use earlier questions
        [$beforeYes1, $fromYes1] = explode('<b>Yes1</b>', $result['html'], 2);
        $this->assertStringContainsString('This uses a question that comes later in the survey.', $beforeYes1);
        $this->assertStringNotContainsString('This uses a question that comes later in the survey.', $fromYes1);
    }
}
