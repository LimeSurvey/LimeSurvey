<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use LimeExpressionManager;
use Yii;

/**
 * Tests that the survey logic file shows the array filter equations on the answer options of list questions.
 */
class SurveyLogicFileListArrayFilterTest extends TestBaseClass
{
    /**
     * Imports the survey: The lists Q2 (with "Other") and Q3 (without) are filtered by the multiple choice Q1 (with "Other").
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_208421_list_array_filter_logic_file.lss');
    }

    /**
     * Each answer option of both lists shows its own filter equation, also the "Other" option of Q2.
     */
    public function testArrayFilterIsShownOnListAnswerOptions()
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

        $html = str_replace('&nbsp;', ' ', strip_tags($result['html']));
        $withOtherStart = strpos($html, 'Q2_other');
        $withoutOtherStart = strpos($html, 'Q3');
        $this->assertNotFalse($withOtherStart);
        $this->assertNotFalse($withoutOtherStart);
        $withOtherPart = substr($html, $withOtherStart, $withoutOtherStart - $withOtherStart);
        $withoutOtherPart = substr($html, $withoutOtherStart);

        // The "Other" option of the list is filtered by the "Other" option of the multiple choice
        $this->assertMatchesRegularExpression('/Q2_other\s*\(\( ! is_empty\(Q1_other\.NAOK\)\)\)/', $withOtherPart);
        foreach (['A1', 'A2', 'A3'] as $code) {
            $this->assertMatchesRegularExpression(
                '/' . $code . '\s*\[VALUE: 0\]\s*\(\( ! is_empty\(Q1_' . $code . '\.NAOK\)\)\)\s*Opt/',
                $withOtherPart
            );
            $this->assertMatchesRegularExpression(
                '/' . $code . '\s*\[VALUE: 0\]\s*\(\( ! is_empty\(Q1_' . $code . '\.NAOK\)\)\)\s*Alt/',
                $withoutOtherPart
            );
        }
    }
}
