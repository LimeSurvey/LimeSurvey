<?php

namespace ls\tests;

/**
 * The "No answer" option is hidden for soft mandatory questions, like for mandatory ones,
 * in 5 point choice, array 10 point, array and dual scale array questions.
 *
 * @see https://bugs.limesurvey.org/view.php?id=18377
 */
class NoAnswerSoftMandatoryTest extends TestBaseClass
{
    /**
     * Import and activate the survey: Each question type twice on one page,
     * soft mandatory (code ending in S) and not mandatory (code ending in N).
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_418377_no_answer_soft_mandatory.lss');
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Reset request and session data
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * @return array<string, string[]>
     */
    public static function questionProvider(): array
    {
        return [
            '5 point choice'    => ['Q5'],
            'array 10 point'    => ['QB'],
            'array'             => ['QF'],
            'array dual scale'  => ['QDual'],
        ];
    }

    /**
     * "No answer" is not rendered for the soft mandatory question, but for the not mandatory one.
     * @param string $title the question code without the mandatory suffix
     * @dataProvider questionProvider
     */
    public function testNoAnswerHiddenForSoftMandatory($title)
    {
        $this->startSurvey();

        $this->assertStringNotContainsString('No answer', $this->render($title . 'S'), "$title soft mandatory");
        $this->assertStringContainsString('No answer', $this->render($title . 'N'), "$title not mandatory");
    }

    /**
     * Render a question of the test survey
     * @param string $title the question code
     * @return string the HTML without comments
     */
    private function render($title)
    {
        $question = \Question::model()->findByAttributes(
            ['sid' => self::$surveyId, 'parent_qid' => 0, 'title' => $title]
        );
        $sgqa = 'Q' . $question->qid;
        $fieldArray = [
            $question->qid, $sgqa, $question->title, $question->title, $question->type, $question->gid, $question->mandatory, 'N'
        ];
        list($html) = $question->getRenderererObject($fieldArray)->render();
        // The templates mention "No answer" in HTML comments
        return preg_replace('/<!--.*?-->/s', '', $html);
    }

    /**
     * Start the survey in group by group mode
     */
    private function startSurvey()
    {
        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \Yii::app()->loadHelper('qanda');
        // Set by the survey runtime and needed by the renderers
        $GLOBALS['thissurvey'] = getSurveyInfo(self::$surveyId);
        if (!defined('SHOW_NO_ANSWER')) {
            \setNoAnswerMode($GLOBALS['thissurvey']);
        }
        \buildsurveysession(self::$surveyId);
        // The session is reset after each test, the renderers need the survey ID of EM in it
        \LimeExpressionManager::SetSurveyId(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );
        \LimeExpressionManager::NavigateForwards();
    }
}
