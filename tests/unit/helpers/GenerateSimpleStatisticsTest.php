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
        Yii::app()->loadHelper('admin/statistics');
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

        $scripts = $doc->getElementsByTagName('script');

        $scriptQ1 = trim($scripts->item(0)->nodeValue);
        $scriptQ2 = trim($scripts->item(1)->nodeValue);

        $questionId1 = $questions[0]->qid;
        $questionId2 = $questions[1]->qid;

        $this->assertStringContainsString("['quid'+'" . $questionId1 . "']", $scriptQ1, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("['quid'+'" . $questionId1 . "']", $scriptQ1, 'The statistics do not contain the correct question id.');

        $this->assertStringContainsString("[2,2,0,0]", $scriptQ1, 'The statistics values are not correct.');
        $this->assertStringContainsString("[3,0,2,0]", $scriptQ2, 'The statistics values are not correct.');
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

        $scripts = $doc->getElementsByTagName('script');

        $scriptQ1 = trim($scripts->item(0)->nodeValue);
        $scriptQ2 = trim($scripts->item(1)->nodeValue);

        $questionId1 = $questions[0]->qid;
        $questionId2 = $questions[1]->qid;

        $this->assertStringContainsString("['quid'+'" . $questionId1 . "']", $scriptQ1, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("['quid'+'" . $questionId1 . "']", $scriptQ1, 'The statistics do not contain the correct question id.');

        $this->assertStringContainsString("[3,3,2]", $scriptQ1, 'The statistics values are not correct.');
        $this->assertStringContainsString("[3,3,2]", $scriptQ2, 'The statistics values are not correct.');
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

        $scripts = $doc->getElementsByTagName('script');

        $scriptQ1 = trim($scripts->item(0)->nodeValue);
        $scriptQ2 = trim($scripts->item(1)->nodeValue);
        $scriptQ3 = trim($scripts->item(2)->nodeValue);
        $scriptQ4 = trim($scripts->item(3)->nodeValue);
        $scriptQ5 = trim($scripts->item(4)->nodeValue);
        $scriptQ6 = trim($scripts->item(5)->nodeValue);

        $questionId1 = $questions[0]->qid;

        $subquestions = $questions[0]->subquestions;

        $this->assertStringContainsString("['quid'+'" . $questionId1 . $subquestions[0]->title . "']", $scriptQ1, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("['quid'+'" . $questionId1 . $subquestions[1]->title . "']", $scriptQ2, 'The statistics do not contain the correct question id.');
    }
}
