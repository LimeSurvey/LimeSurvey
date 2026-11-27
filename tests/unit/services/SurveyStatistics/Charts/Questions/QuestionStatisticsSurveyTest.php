<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\QuestionStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use ls\tests\TestBaseClass;
use Yii;

/**
 * Runs the question statistics against a real survey with a response.
 *
 * @see https://bugs.limesurvey.org/view.php?id=20892
 */
class QuestionStatisticsSurveyTest extends TestBaseClass
{
    /** @var \Question List question with the answer options "Afrika", ... */
    private static $listQuestion;

    /**
     * Imports and activates the survey and adds one completed response.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss');
        self::$listQuestion = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G02Q03']);

        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();
        Yii::app()->db->schema->refresh();

        \SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'en',
            'submitdate' => date('Y-m-d H:i:s'),
            'Q' . self::$listQuestion->qid => 'AO01',
        ]);
    }

    /**
     * The statistics query loads the answer texts in the statistics language.
     * It must run on every database: MSSQL fails if a named parameter is used twice.
     *
     * @return void
     */
    public function testListQuestionChartHasAnswerTexts(): void
    {
        $charts = (new QuestionStatistics())->run(self::$surveyId, 'en');

        $listChart = null;
        foreach ($charts as $chart) {
            if ($chart instanceof StatisticsChartDTO && in_array('Afrika', $chart->toArray()['legend'], true)) {
                $listChart = $chart->toArray();
            }
        }

        $this->assertNotNull($listChart, 'No chart with the answer text of the list question.');
        $afrika = array_values(array_filter($listChart['data'], static fn($item) => $item['title'] === 'Afrika'));
        $this->assertSame(1, $afrika[0]['value']);
    }
}
