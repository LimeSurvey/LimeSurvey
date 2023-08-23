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

        $statisticsData = $this->getStatisticsData($questions, $scripts);

        foreach ($statisticsData as $data) {
            $this->assertStringContainsString("['quid'+'" . $data['quid'] . "']", $data['script'], 'The statistics do not contain the correct question id.');
            //$this->assertStringContainsString("[2,2,4,0]", $data['script'], 'The statistics values are not correct.');
        }
    }

    private function getStatisticsData($questions, $scripts)
    {
        $questionsLength = count($questions);
        $statisticsData = array();
        $scriptCounter = 0;

        for ($i = 0; $i < $questionsLength; $i++) {
            $subquestions = $questions[$i]->subquestions;

            if (empty($subquestions)) {
                $statisticsData[$i]['script'] = trim($scripts->item($i)->nodeValue);
                $statisticsData[$i]['quid'] = $questions[$i]->qid;
                continue;
            }

            $subquestionsLength = count($subquestions);
            for ($j = 0; $j < $subquestionsLength; $j++) {
                $statisticsData[$scriptCounter]['script'] = trim($scripts->item($scriptCounter)->nodeValue);
                $statisticsData[$scriptCounter]['quid'] = $questions[$i]->qid . $subquestions[$j]->title;

                $scriptCounter++;
            }
        }

        return $statisticsData;
    }

    public function StatisticsForThreeQuestions()
    {
        // Form SGQA identifiers.
        $allQuestions = \Question::model()->getQuestionList(self::$surveyId);
        $summary = createCompleteSGQA(self::$surveyId, $allQuestions, null);

        $helper = new \statistics_helper();
        $statistics = $helper->generate_simple_statistics(self::$surveyId, $summary, $summary, 1, 'html', 'DD');

        $doc = new \DOMDocument();
        $doc->loadHtml($statistics);

        $scripts = $doc->getElementsByTagName('script');

        $scriptQ1 = trim($scripts->item(0)->nodeValue);
        $scriptQ2 = trim($scripts->item(1)->nodeValue);
        $scriptQ3 = trim($scripts->item(2)->nodeValue);

        $questionId1 = $allQuestions[0]->qid;
        $questionId2 = $allQuestions[1]->qid;
        $questionId3 = $allQuestions[2]->qid;

        $this->assertStringContainsString("['quid'+'" . $questionId1 . "']", $scriptQ1, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("[2,2,4,0]", $scriptQ1, 'The statistics values are not correct.');

        $this->assertStringContainsString("['quid'+'" . $questionId2 . "']", $scriptQ2, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("[5,3,1,0]", $scriptQ2, 'The statistics values are not correct.');

        $this->assertStringContainsString("['quid'+'" . $questionId3 . "']", $scriptQ3, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("[1,5,3,0]", $scriptQ3, 'The statistics values are not correct.');
    }
}
