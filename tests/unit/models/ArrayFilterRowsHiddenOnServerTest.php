<?php

namespace ls\tests;

/**
 * Array rows filtered out by an array filter from a previous page are already marked
 * as irrelevant in the HTML sent by the server, not only after the JavaScript ran.
 *
 * @see https://bugs.limesurvey.org/view.php?id=15102
 */
class ArrayFilterRowsHiddenOnServerTest extends TestBaseClass
{
    /**
     * Import and activate the survey: Multiple choice Filter (SQ1 to SQ4) on the first page
     * filters one array question of each type (rows SQ1 to SQ4) on the second page.
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
        parent::tearDown();
    }

    /**
     * Only SQ1 and SQ3 are checked in Filter: The rows SQ2 and SQ4 of every array question
     * are rendered as hidden (or disabled with array_filter_style), the others without a relevance class.
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

        $expectedClasses = [
            'QA'         => 'ls-hidden',
            'QB'         => 'ls-hidden',
            'QC'         => 'ls-hidden',
            'QE'         => 'ls-hidden',
            'QF'         => 'ls-hidden',
            'QFdrop'     => 'ls-hidden',
            'QNum'       => 'ls-hidden',
            'QTxt'       => 'ls-hidden',
            'QDual'      => 'ls-hidden',
            'QDualDrop'  => 'ls-hidden',
            'QAdisabled' => 'ls-disabled',
        ];
        foreach ($expectedClasses as $title => $filteredClass) {
            $question = $this->getQuestion($title);
            $rowClasses = $this->getRowClasses($question);
            foreach (['SQ1', 'SQ3'] as $code) {
                $this->assertNotContains('ls-irrelevant', $rowClasses[$code], "$title $code is relevant");
            }
            foreach (['SQ2', 'SQ4'] as $code) {
                $this->assertContains('ls-irrelevant', $rowClasses[$code], "$title $code is irrelevant");
                $this->assertContains($filteredClass, $rowClasses[$code], "$title $code has class $filteredClass");
            }
        }
    }

    /**
     * Render a question and return the classes of its rows
     * @param \Question $question the question to render
     * @return array<string, string[]> the classes, indexed by subquestion code
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
            $rows = $xpath->query('//tr[@id="javatbd' . $this->getSubquestionField($question, $code) . '"]');
            $this->assertSame(1, $rows->length, "{$question->title} has one row $code");
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
