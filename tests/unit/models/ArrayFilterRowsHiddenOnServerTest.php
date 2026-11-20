<?php

namespace ls\tests;

/**
 * Rows filtered out by an array filter from a previous page are left out of the HTML sent by the server,
 * rows filtered by a question of the same page are only marked as hidden, so JavaScript can show them again.
 *
 * @see https://bugs.limesurvey.org/view.php?id=15102
 */
class ArrayFilterRowsHiddenOnServerTest extends TestBaseClass
{
    /**
     * Import and activate the survey: Multiple choice Filter (SQ1 to SQ4) on the first page
     * filters one array question of each type and a multiple choice question (rows SQ1 to SQ4) on the second page.
     * The questions ending in "dyn" are filtered by Filter and Filter2, a multiple choice question of the second page.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_953474_array_filter_rows_hidden.lss');
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
        \Yii::app()->setConfig('deletenonvalues', 1);
        parent::tearDown();
    }

    /**
     * With deletenonvalues off, the answer of a row that is left out of the page is kept when the page is submitted again
     */
    public function testAnswerOfRowLeftOutIsKeptWithoutDeleteNonValues()
    {
        \Yii::app()->setConfig('deletenonvalues', 0);
        $filter = $this->getQuestion('Filter');
        $arrayText = $this->getQuestion('QTxt');
        $row = $this->getSubquestionField($arrayText, 'SQ2');
        $column = \Question::model()->findByAttributes(['parent_qid' => $arrayText->qid, 'title' => 'X1', 'scale_id' => 1]);
        $field = $row . '_S' . $column->qid;
        $pageTwo = [
            'relevance' . $arrayText->qid => 1,
            'relevanceG1'                 => 1,
            'thisstep'                    => 2,
            'sid'                         => self::$surveyId,
        ];

        $this->startSurvey();
        // All rows are relevant: Answer SQ2 and go back
        $this->submitFilter(['SQ1', 'SQ2', 'SQ3', 'SQ4']);
        $_POST = $pageTwo + ['relevance' . $row => 1, $field => 'kept', 'moveprev' => 'moveprev'];
        \LimeExpressionManager::NavigateBackwards();
        $this->assertSame('kept', $_SESSION['responses_' . self::$surveyId][$field], 'The answer is stored');

        // SQ2 is filtered out now and not in the page: Submitting the page keeps its answer
        $this->submitFilter(['SQ1', 'SQ3']);
        $_POST = $pageTwo + ['relevance' . $row => 0, 'movenext' => 'movenext'];
        \LimeExpressionManager::NavigateForwards();
        $this->assertSame('kept', $_SESSION['responses_' . self::$surveyId][$field], 'The answer of the row left out is kept');
    }

    /**
     * Submit the first page with the given filter options checked
     * @param string[] $checked the checked subquestion codes of Filter
     */
    private function submitFilter(array $checked)
    {
        $filter = $this->getQuestion('Filter');
        $_POST = [
            'relevance' . $filter->qid => 1,
            'relevanceG0'              => 1,
            'movenext'                 => 'movenext',
            'thisstep'                 => 1,
            'sid'                      => self::$surveyId,
        ];
        foreach ($checked as $code) {
            $_POST[$this->getSubquestionField($filter, $code)] = 'Y';
        }
        \LimeExpressionManager::NavigateForwards();
    }

    /**
     * Only SQ1 and SQ3 are checked in Filter: The rows SQ2 and SQ4 of the questions filtered by Filter alone
     * are not rendered (disabled with array_filter_style), the rows of the questions filtered by Filter2 too are rendered hidden.
     */
    public function testFilteredRowsAreRenderedIrrelevant()
    {
        $filter = $this->getQuestion('Filter');

        $this->startSurvey();
        $_POST = [
            'relevance' . $filter->qid             => 1,
            'relevanceG0'                          => 1,
            $this->getSubquestionField($filter, 'SQ1') => 'Y',
            $this->getSubquestionField($filter, 'SQ3') => 'Y',
            'movenext'                             => 'movenext',
            'thisstep'                             => 1,
            'sid'                                  => self::$surveyId,
        ];
        \LimeExpressionManager::NavigateForwards();

        // Nothing on the second page can change the filter: The filtered rows are left out
        foreach (['QA', 'QB', 'QC', 'QE', 'QF', 'QFdrop', 'QNum', 'QTxt', 'QDual', 'QDualDrop', 'QM'] as $title) {
            $rowClasses = $this->getRowClasses($this->getQuestion($title));
            foreach (['SQ1', 'SQ3'] as $code) {
                $this->assertNotNull($rowClasses[$code], "$title $code is rendered");
                $this->assertNotContains('ls-irrelevant', $rowClasses[$code], "$title $code is relevant");
            }
            foreach (['SQ2', 'SQ4'] as $code) {
                $this->assertNull($rowClasses[$code], "$title $code is left out of the HTML");
            }
        }

        // Rows only disabled by the filter stay in the HTML
        $rowClasses = $this->getRowClasses($this->getQuestion('QAdisabled'));
        foreach (['SQ2', 'SQ4'] as $code) {
            $this->assertNotNull($rowClasses[$code], "QAdisabled $code is rendered");
            $this->assertContains('ls-disabled', $rowClasses[$code], "QAdisabled $code is disabled");
        }

        // Filter2 is on the second page, so the rows can become relevant again: They are rendered hidden
        foreach (['QAdyn', 'QTxtdyn', 'QMdyn'] as $title) {
            $rowClasses = $this->getRowClasses($this->getQuestion($title));
            foreach (['SQ2', 'SQ4'] as $code) {
                $this->assertNotNull($rowClasses[$code], "$title $code is rendered");
                $this->assertContains('ls-hidden', $rowClasses[$code], "$title $code is hidden");
            }
        }
    }

    /**
     * Render a question and return the classes of its rows
     * @param \Question $question the question to render
     * @return array<string, string[]|null> the classes, indexed by subquestion code, null for a row that is not rendered
     */
    private function getRowClasses($question)
    {
        $sgqa = 'Q' . $question->qid;
        $fieldArray = [$question->qid, $sgqa, $question->title, $question->title, $question->type, $question->gid, 'N', 'Y'];
        list($html) = $question->getRenderererObject($fieldArray)->render();

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);

        $rowClasses = [];
        foreach (['SQ1', 'SQ2', 'SQ3', 'SQ4'] as $code) {
            $rows = $xpath->query('//*[@id="javatbd' . $this->getSubquestionField($question, $code) . '"]');
            $this->assertLessThan(2, $rows->length, "{$question->title} has at most one row $code");
            if ($rows->length === 0) {
                $rowClasses[$code] = null;
                continue;
            }
            /** @var \DOMElement $row */
            $row = $rows->item(0);
            $rowClasses[$code] = preg_split('/\s+/', trim($row->getAttribute('class')));
        }
        return $rowClasses;
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

    /**
     * Get a question of the test survey by its code
     * @param string $title the question code
     * @return \Question
     */
    private function getQuestion($title)
    {
        return \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'title' => $title]);
    }

    /**
     * Get the response field name of a subquestion (scale 0)
     * @param \Question $question the parent question
     * @param string $title the subquestion code
     * @return string
     */
    private function getSubquestionField($question, $title)
    {
        $subquestion = \Question::model()->findByAttributes(['parent_qid' => $question->qid, 'title' => $title, 'scale_id' => 0]);
        return 'Q' . $question->qid . '_S' . $subquestion->qid;
    }
}
