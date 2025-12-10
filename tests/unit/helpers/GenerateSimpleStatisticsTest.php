<?php

namespace ls\tests;

use Yii;

/**
 * Tests for statistics_helper::generate_simple_statistics().
 */
class GenerateSimpleStatisticsTest extends TestBaseClass
{
    private static $questions = array();

    public static function setUpBeforeClass(): void
    {
        Yii::app()->loadHelper('admin.statistics');
        Yii::app()->loadHelper('common');

        parent::setUpBeforeClass();

        Yii::app()->setController(new DummyController('dummyid'));

        // Import survey
        $filename = self::$surveysFolder . '/survey_simple_statistics.lsa';
        self::importSurvey($filename);

        $questionGroups = \QuestionGroup::model()->findAllByAttributes(['sid' => self::$surveyId]);

        foreach ($questionGroups as $group) {
            self::$questions[$group->getprimaryTitle()] = \Question::model()->findAllByAttributes(['gid' => $group->gid, 'parent_qid' => 0]);
        }
    }

    public function testGenerateSimpleStatisticsForSingleChoiceQuestions()
    {
        $questions = self::$questions['Single choice'];

        // Form SGQA identifiers.
        $summary = createCompleteSGQA(self::$surveyId, $questions, null);

        $helper = new \statistics_helper();
        $statistics = $helper->generate_simple_statistics(self::$surveyId, $summary, $summary, 1, 'html', 'DD');

        $doc = new \DOMDocument();
        $doc->loadHtml($statistics);

        // Get the script string based on the question id and order the data by title.
        $assertions = array();
        $scripts = $doc->getElementsByTagName('script');

        foreach ($questions as $question) {
            foreach ($scripts as $script) {
                if (str_contains($script->nodeValue, "['quid'+'Q" . $question->qid . "']")) {
                    $assertions[$question->title]['script'] = trim($script->nodeValue);
                    break;
                }
            }
        }

        // SCRQ stands for single choice radio question.
        $this->assertArrayHasKey('SCRQ', $assertions, 'Apparently the single choice radio question was not set.');
        // SCDQ stands for single choice dropdown question.
        $this->assertArrayHasKey('SCDQ', $assertions, 'Apparently the single choice dropdown question was not set.');

        // Asserting the data for the single choice dropdown question is correct.
        $this->assertStringContainsString('[3,0,2,0]', $assertions['SCDQ']['script'], 'The statistics values are not correct.');
        // Asserting the data for the single choice radio question is correct.
        $this->assertStringContainsString('[2,2,0,0]', $assertions['SCRQ']['script'], 'The statistics values are not correct.');
    }

    public function testGenetareSimpleStatisticsForMultipliChoiceQuestions()
    {
        $questions = self::$questions['Multiple choice'];

        // Form SGQA identifiers.
        $summary = createCompleteSGQA(self::$surveyId, $questions, null);

        $helper = new \statistics_helper();
        $statistics = $helper->generate_simple_statistics(self::$surveyId, $summary, $summary, 1, 'html', 'DD');

        $doc = new \DOMDocument();
        $doc->loadHtml($statistics);

        // Get the script string based on the question id and order the data by title.
        $assertions = array();
        $scripts = $doc->getElementsByTagName('script');

        foreach ($questions as $question) {
            foreach ($scripts as $script) {
                if (str_contains($script->nodeValue, "['quid'+'Q" . $question->qid . "']")) {
                    $assertions[$question->title]['script'] = trim($script->nodeValue);
                    break;
                }
            }
        }

        // MCBQ stands for multiple choice bootstrap button question.
        $this->assertArrayHasKey('MCBQ', $assertions, 'Apparently the multiple choice bootstrap button question was not set.');
        // MCCQ stands for multiple choice checkbox question.
        $this->assertArrayHasKey('MCCQ', $assertions, 'Apparently the multiple choice checkbox question was not set.');

        // Asserting the data for the multiple choice bootstrap button question is correct.
        $this->assertRegExp('/^.+grawdata : \[3,3,2,\"?1\"?\]/m', $assertions['MCBQ']['script'], 'The statistics values are not correct.');
        // Asserting the data for the multiple choice checkbox question is correct.
        $this->assertStringContainsString('[4,3,2]', $assertions['MCCQ']['script'], 'The statistics values are not correct.');
    }

    public function testGenetareSimpleStatisticsForArrayQuestions()
    {
        $questions = self::$questions['Arrays'];

        // Form SGQA identifiers.
        $summary = createCompleteSGQA(self::$surveyId, $questions, null);

        $helper = new \statistics_helper();
        $statistics = $helper->generate_simple_statistics(self::$surveyId, $summary, $summary, 1, 'html', 'DD');

        $doc = new \DOMDocument();
        $doc->loadHtml($statistics);

        // Get the script string based on the question id and order the data by title.
        $assertions = array();
        $scripts = $doc->getElementsByTagName('script');

        $arrayQuestionId = '';
        $arrayByColumnQuestionId = '';

        foreach ($questions as $key => $question) {
            $subquestions = $question->subquestions;

            // AGAQ stands for array group, array question.
            if ($question->title === 'AGAQ') {
                $arrayQuestionId = $question->qid;
            } elseif ($question->title === 'AGCQ') { // Array group column question.
                $arrayByColumnQuestionId = $question->qid;
            }

            foreach ($subquestions as $subquestion) {
                foreach ($scripts as $script) {
                    if (str_contains($script->nodeValue, "['quid'+'Q" . $question->qid . "_S" . $subquestion->qid . "']")) {
                        $assertions[$question->qid . $subquestion->title]['script'] = trim($script->nodeValue);
                        break;
                    }
                }
            }
        }

        // Asserting the data for subquestion one in array question is correct.
        $this->assertStringContainsString('[1,1,1,0]', $assertions[$arrayQuestionId . 'SQ001']['script'], 'The statistics values are not correct.');

        // Asserting the data for subquestion two in array question is correct.
        $this->assertStringContainsString('[2,2,1,0]', $assertions[$arrayQuestionId . 'SQ002']['script'], 'The statistics values are not correct.');

        // Asserting the data for subquestion three in array question is correct.
        $this->assertStringContainsString('[1,2,2,0]', $assertions[$arrayQuestionId . 'SQ003']['script'], 'The statistics values are not correct.');

        // Asserting the data for subquestion one in array by column question is correct.
        $this->assertStringContainsString('[2,2,1,0]', $assertions[$arrayByColumnQuestionId . 'SQ001']['script'], 'The statistics values are not correct.');

        // Asserting the data for subquestion two in array by column question is correct.
        $this->assertStringContainsString('[1,2,1,0]', $assertions[$arrayByColumnQuestionId . 'SQ002']['script'], 'The statistics values are not correct.');

        // Asserting the data for subquestion three in array by column question is correct.
        $this->assertStringContainsString('[2,0,2,0]', $assertions[$arrayByColumnQuestionId . 'SQ003']['script'], 'The statistics values are not correct.');
    }
}
