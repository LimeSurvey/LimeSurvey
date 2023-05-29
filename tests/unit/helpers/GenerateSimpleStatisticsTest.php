<?php

namespace ls\tests;

use Yii;

/**
 * Tests for statistics_helper::generate_simple_statistics().
 */
class GenerateSimpleStatisticsTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        Yii::app()->loadHelper('admin/statistics');
        Yii::app()->loadHelper('common');

        parent::setUpBeforeClass();

        Yii::app()->setController(new DummyController('dummyid'));

        // Import survey
        $filename = self::$surveysFolder . '/survey_simple_statistics.lsa';
        self::importSurvey($filename);
    }

    public function testStatisticsForThreeQuestions()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');    

        // Form SGQA identifiers.
        echo 'Fetching Questions';
        $allQuestions = \Question::model()->getQuestionList(self::$surveyId);
        var_dump($allQuestions);
        echo 'createCompleteSGQA';
        $summary = createCompleteSGQA(self::$surveyId, $allQuestions, null);

        echo 'statistics_helper->generate_simple_statistics';
        $helper = new \statistics_helper();
        $statistics = $helper->generate_simple_statistics(self::$surveyId, $summary, $summary, 1, 'html', 'DD');

        var_dump($statistics);
        echo 'Dom Parsing';
        $doc = new \DOMDocument();
        $doc->loadHtml($statistics);

        echo "getElementsByTagName('script')";
        $scripts = $doc->getElementsByTagName('script');

        echo "Node Values";
        $scriptQ1 = trim($scripts->item(0)->nodeValue);
        $scriptQ2 = trim($scripts->item(1)->nodeValue);
        $scriptQ3 = trim($scripts->item(2)->nodeValue);

        echo "QIDs";
        $questionId1 = $allQuestions[0]->qid;
        $questionId2 = $allQuestions[1]->qid;
        $questionId3 = $allQuestions[2]->qid;

        echo "Asserts";
        $this->assertStringContainsString("['quid'+'" . $questionId1 . "']", $scriptQ1, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("[2,2,4,0]", $scriptQ1, 'The statistics values are not correct.');

        $this->assertStringContainsString("['quid'+'" . $questionId2 . "']", $scriptQ2, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("[5,3,1,0]", $scriptQ2, 'The statistics values are not correct.');

        $this->assertStringContainsString("['quid'+'" . $questionId3 . "']", $scriptQ3, 'The statistics do not contain the correct question id.');
        $this->assertStringContainsString("[1,5,3,0]", $scriptQ3, 'The statistics values are not correct.');
    }
}
