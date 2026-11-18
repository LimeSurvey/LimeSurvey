<?php

namespace ls\tests;

/**
 * Submitting a page with a mandatory question that has a condition on the "No answer" option of a single choice
 * question on the same page: The server must agree with the browser on whether the question is shown.
 */
class NoAnswerConditionSubmitTest extends TestBaseClass
{
    /**
     * Import the survey and make the question with the "No answer" condition mandatory.
     * The survey stays inactive, so the type of the list question can change between tests.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        self::importSurvey(self::$surveysFolder . '/survey_fixMovedQuestionConditionsDefault.lss');
        \LimeExpressionManager::UpgradeConditionsToRelevance(self::$surveyId);

        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G01Q02']);
        $question->mandatory = 'Y';
        $question->save();

        $survey = \Survey::model()->findByPk(self::$surveyId);
        $survey->shownoanswer = 'Y';
        $survey->save();
    }

    /**
     * Reset request and session data.
     * @return void
     */
    public function tearDown(): void
    {
        $_POST = [];
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * Variants of the list question: Question type, "Other" option and the suffix of the extra field that is
     * always posted (the comment or the "Other" text), if any.
     * @return array<string, array{string, string, string|null}>
     */
    public static function listQuestionProvider()
    {
        return [
            'list' => [\Question::QT_L_LIST, 'N', null],
            'list with comment' => [\Question::QT_O_LIST_WITH_COMMENT, 'N', '_Ccomment'],
            'list with other' => [\Question::QT_L_LIST, 'Y', '_Cother'],
            // An untouched dropdown stays on its disabled "Please choose...", which isn't posted
            'list dropdown' => [\Question::QT_EXCLAMATION_LIST_DROPDOWN, 'N', null],
            'list dropdown with other' => [\Question::QT_EXCLAMATION_LIST_DROPDOWN, 'Y', '_Cother'],
        ];
    }

    /**
     * An untouched "No answer" radio hides the mandatory question in the browser, so it must not block the page.
     * @param string $type The type of the list question
     * @param string $other Whether the list question has an "Other" option (Y or N)
     * @param string|null $extraFieldSuffix The suffix of the extra field of the list question, if any
     * @return void
     * @dataProvider listQuestionProvider
     */
    public function testUntouchedNoAnswerDoesNotBlockNavigation($type, $other, $extraFieldSuffix)
    {
        $moveResult = $this->submitFirstPage($type, $other, $extraFieldSuffix, false);

        $this->assertFalse((bool) $moveResult['mandViolation'], 'The hidden mandatory question must not block the page');
        $this->assertNotEquals(0, $moveResult['gseq'], 'The survey must move to the next page');
    }

    /**
     * An explicitly checked "No answer" shows the mandatory question, so leaving it empty is a violation.
     * @param string $type The type of the list question
     * @param string $other Whether the list question has an "Other" option (Y or N)
     * @param string|null $extraFieldSuffix The suffix of the extra field of the list question, if any
     * @return void
     * @dataProvider listQuestionProvider
     */
    public function testExplicitNoAnswerRequiresMandatoryQuestion($type, $other, $extraFieldSuffix)
    {
        $moveResult = $this->submitFirstPage($type, $other, $extraFieldSuffix, true);

        $this->assertTrue((bool) $moveResult['mandViolation'], 'The shown mandatory question must be answered');
        $this->assertSame(0, $moveResult['gseq'], 'The survey must stay on the first page');
    }

    /**
     * An explicit "No answer" is remembered, so the page can show it again after a validation error.
     * @return void
     */
    public function testExplicitNoAnswerIsRemembered()
    {
        list(, , $listSgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);

        $this->submitFirstPage(\Question::QT_L_LIST, 'N', null, true);

        $this->assertTrue(\LimeExpressionManager::isNoAnswerChosen($listSgqa));
    }

    /**
     * An explicit "No answer" isn't remembered when the question is irrelevant, since its answer is discarded.
     * @return void
     */
    public function testExplicitNoAnswerOfIrrelevantQuestionIsForgotten()
    {
        list($listQuestion, , $listSgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        $listQuestion->relevance = '0';
        $this->assertTrue($listQuestion->save(), 'The list question must be saved');
        try {
            $this->submitFirstPage(\Question::QT_L_LIST, 'N', null, true);

            $this->assertFalse(\LimeExpressionManager::isNoAnswerChosen($listSgqa));
        } finally {
            $listQuestion->relevance = '1';
            $listQuestion->save();
        }
    }

    /**
     * Start the survey in group by group mode and submit the first page with the mandatory question left empty.
     * @param string $type The type of the list question
     * @param string $other Whether the list question has an "Other" option (Y or N)
     * @param string|null $extraFieldSuffix The suffix of the extra field of the list question, if any
     * @param boolean $checkNoAnswer Whether the "No answer" radio of the list question is checked
     * @return array The move result
     */
    private function submitFirstPage($type, $other, $extraFieldSuffix, $checkNoAnswer)
    {
        list($listQuestion, , $listSgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        list($mandatoryQuestion, , $mandatorySgqa) = self::$testHelper->getSgqa('G01Q02', self::$surveyId);
        $listQuestion->type = $type;
        $listQuestion->other = $other;
        $this->assertTrue($listQuestion->save(), 'The list question must be saved');

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \LimeExpressionManager::SetDirtyFlag();
        \buildsurveysession(self::$surveyId);
        \LimeExpressionManager::SetSurveyId(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            true,
            0
        );
        \LimeExpressionManager::NavigateForwards();

        $_POST = [
            'relevance' . $listQuestion->qid => 1,
            'relevance' . $mandatoryQuestion->qid => $checkNoAnswer ? 1 : 0,
            'relevanceG0' => 1,
            'movenext' => 'movenext',
            'thisstep' => 1,
            'sid' => self::$surveyId,
            $mandatorySgqa => '',
        ];
        if ($extraFieldSuffix !== null) {
            // The comment or "Other" text field is posted even when the radios are untouched
            $_POST[$listSgqa . $extraFieldSuffix] = '';
        }
        if ($checkNoAnswer) {
            $_POST[$listSgqa] = '';
        }

        return \LimeExpressionManager::NavigateForwards();
    }
}
