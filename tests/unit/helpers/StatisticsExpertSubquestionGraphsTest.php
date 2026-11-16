<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that the expert mode statistics (statistics_helper::generate_html_chartjs_statistics())
 * give every subquestion of an array question its own graph (mantis #20858).
 */
class StatisticsExpertSubquestionGraphsTest extends TestBaseClass
{
    /** @var \Question[] Array questions of the test survey */
    private static $questions = [];

    /** @var string HTML of the expert mode statistics for the array questions */
    private static $statistics = '';

    /**
     * Imports the test survey and generates the expert mode statistics for its array questions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        Yii::app()->loadHelper('admin.statistics');
        Yii::app()->loadHelper('common');

        parent::setUpBeforeClass();

        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/survey_simple_statistics.lsa', 1, 920858);

        foreach (\QuestionGroup::model()->findAllByAttributes(['sid' => self::$surveyId]) as $group) {
            if ($group->getprimaryTitle() === 'Arrays') {
                self::$questions = \Question::model()->findAllByAttributes(['gid' => $group->gid, 'parent_qid' => 0]);
            }
        }

        $summary = getStatisticsFieldNames(self::$surveyId, self::$questions, null);
        $helper = new \statistics_helper();
        self::$statistics = $helper->generate_html_chartjs_statistics(self::$surveyId, $summary, $summary, 1, 'html', 'DD');
    }

    /**
     * Returns the graph ID used in the statistics output for each subquestion of the array questions.
     *
     * @return array<string, string> Graph ID by "question code / subquestion code"
     */
    private function getGraphIds(): array
    {
        $graphIds = [];
        foreach (self::$questions as $question) {
            foreach ($question->subquestions as $subquestion) {
                $graphIds[$question->title . ' / ' . $subquestion->title] = $question->qid . '_S' . $subquestion->qid;
            }
        }
        return $graphIds;
    }

    /**
     * Returns the graph data of the statistics output for a graph ID.
     *
     * The counts are cast to integers, as some database drivers return them as strings.
     *
     * @param string $graphId Graph ID, as returned by getGraphIds()
     * @return int[][] The answer counts of each graph data block found for the ID
     */
    private function getGraphData(string $graphId): array
    {
        $pattern = "/statisticsData\['quid'\+'" . preg_quote($graphId, '/') . "'\] = \{.*?grawdata : (\[[^\]]*\])/s";
        preg_match_all($pattern, self::$statistics, $matches);
        return array_map(
            function (string $json): array {
                return array_map('intval', json_decode($json, true));
            },
            $matches[1]
        );
    }

    /**
     * Every subquestion must have exactly one canvas and one graph data block of its own.
     *
     * @return void
     */
    public function testEverySubquestionHasItsOwnGraph()
    {
        $graphIds = $this->getGraphIds();
        $this->assertCount(6, $graphIds, 'The test survey should have two array questions with three subquestions each.');

        foreach ($graphIds as $name => $graphId) {
            $this->assertSame(
                1,
                substr_count(self::$statistics, 'id="chartjs-' . $graphId . '"'),
                'There should be exactly one canvas for ' . $name . '.'
            );
            $this->assertSame(
                1,
                substr_count(self::$statistics, 'id="chartjs-container-' . $graphId . '"'),
                'There should be exactly one graph container for ' . $name . '.'
            );
            $this->assertCount(1, $this->getGraphData($graphId), 'There should be exactly one graph data block for ' . $name . '.');
        }
    }

    /**
     * The graph data of a subquestion must be the answer counts of that subquestion:
     * One count per answer option, then "No answer" and "Not completed or not displayed".
     *
     * @return void
     */
    public function testGraphDataBelongsToTheSubquestion()
    {
        $expected = [
            'AGAQ / SQ001' => [[1, 1, 1, 0, 3]],
            'AGAQ / SQ002' => [[2, 2, 1, 0, 1]],
            'AGAQ / SQ003' => [[1, 2, 2, 0, 1]],
            'AGCQ / SQ001' => [[2, 2, 1, 0, 1]],
            'AGCQ / SQ002' => [[1, 2, 1, 0, 2]],
            'AGCQ / SQ003' => [[2, 0, 2, 0, 2]],
        ];
        $graphIds = $this->getGraphIds();

        $actual = [];
        foreach ($graphIds as $name => $graphId) {
            $actual[$name] = $this->getGraphData($graphId);
        }
        ksort($actual);

        $this->assertSame($expected, $actual);
    }
}
