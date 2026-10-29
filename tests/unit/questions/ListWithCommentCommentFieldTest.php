<?php

namespace ls\tests;

/**
 * The comment of a List with comment question is saved and displayed again,
 * in the radio list layout as well as in the dropdown layout.
 */
class ListWithCommentCommentFieldTest extends TestBaseClass
{
    /** @var string a comment with HTML special characters */
    private const COMMENT = 'a < b & "c"';

    /**
     * Import and activate the survey: QList (radio list layout) and QDrop (dropdown layout) on one page.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_718593_list_with_comment.lss');
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
    public static function layoutProvider(): array
    {
        return [
            'radio list layout' => ['QList'],
            'dropdown layout'   => ['QDrop'],
        ];
    }

    /**
     * Post a comment with the name of the rendered comment textarea: It is saved in the comment
     * response field and rendered again unchanged.
     * @param string $title the question code
     * @dataProvider layoutProvider
     */
    public function testCommentIsSavedAndRenderedAgain($title)
    {
        $question = \Question::model()->findByAttributes(
            ['sid' => self::$surveyId, 'parent_qid' => 0, 'title' => $title]
        );
        $commentField = 'Q' . $question->qid . '_Ccomment';
        $fieldMap = createFieldMap(\Survey::model()->findByPk(self::$surveyId), 'short', false, false);
        $this->assertArrayHasKey($commentField, $fieldMap, 'The comment field is in the fieldmap');

        $this->startSurvey();
        $textarea = $this->getCommentTextarea($question);
        $this->assertSame(
            $commentField,
            $textarea->getAttribute('name'),
            'The comment textarea posts to the comment field'
        );

        $_POST = [
            'relevance' . $question->qid    => 1,
            'relevanceG0'                   => 1,
            $textarea->getAttribute('name') => self::COMMENT,
            'thisstep'                      => 1,
            'sid'                           => self::$surveyId,
        ];
        \LimeExpressionManager::JumpTo(1, false, true, true);
        $this->assertSame(
            self::COMMENT,
            $_SESSION['responses_' . self::$surveyId][$commentField],
            'The posted comment is saved'
        );

        $this->assertSame(
            self::COMMENT,
            $this->getCommentTextarea($question)->textContent,
            'The saved comment is displayed unchanged'
        );
    }

    /**
     * Render a question and return its comment textarea
     * @param \Question $question the question to render
     * @return \DOMElement
     */
    private function getCommentTextarea($question)
    {
        $sgqa = 'Q' . $question->qid;
        $fieldArray = [
            $question->qid, $sgqa, $question->title, $question->title, $question->type, $question->gid, 'N', 'N'
        ];
        list($html) = $question->getRenderererObject($fieldArray)->render();

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $textareas = $dom->getElementsByTagName('textarea');
        $this->assertSame(1, $textareas->length, "{$question->title} has one comment textarea");
        return $textareas->item(0);
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
