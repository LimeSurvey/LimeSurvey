<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\QuestionStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatchTestHelper;
use PHPUnit\Framework\TestCase;
use Question;
use ReflectionMethod;
use ReflectionProperty;

class QuestionStatisticsTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    /**
     * @testdox Map and Browser Detection questions produce independent response counts
     */
    public function testMapAndBrowserDetectionProduceIndependentStatistics()
    {
        $statistics = new QuestionStatistics();
        $factoriesProperty = new ReflectionProperty($statistics, 'factories');
        $factories = $factoriesProperty->getValue($statistics);
        $batch = new ResponseAggregateBatch(1);

        $mapPlan = $this->buildPlan($statistics, $factories, $batch, [
            'sid' => 1,
            'gid' => 1,
            'qid' => 70,
            'title' => 'Q70',
            'type' => Question::QT_J_MAP,
            'question' => 'Map location',
        ]);
        $browserDetectionPlan = $this->buildPlan($statistics, $factories, $batch, [
            'sid' => 1,
            'gid' => 1,
            'qid' => 71,
            'title' => 'Q71',
            'type' => Question::QT_S_SHORT_FREE_TEXT,
            'question' => 'Browser detection',
        ]);

        $totalAlias = $batch->countTotal();
        $mapAlias = $batch->countNonEmpty('Q70');
        $browserDetectionAlias = $batch->countNonEmpty('Q71');
        $this->injectBatchResults($batch, [
            $totalAlias => 10,
            $mapAlias => 4,
            $browserDetectionAlias => 7,
        ]);

        $this->assertSame('Map location', $mapPlan['title']);
        $this->assertSame('Browser detection', $browserDetectionPlan['title']);
        $this->assertSame(4, $mapPlan['data'][0]['value']());
        $this->assertSame(7, $browserDetectionPlan['data'][0]['value']());
        $this->assertSame(6, $mapPlan['data'][1]['value']());
        $this->assertSame(3, $browserDetectionPlan['data'][1]['value']());
    }

    public function testMapTypeIsReportedAsMap(): void
    {
        $statistics = new QuestionStatistics();
        (new ReflectionProperty($statistics, 'language'))->setValue($statistics, 'en');
        $chart = new StatisticsChartDTO('', [], [], null, [
            'question' => [
                'qid' => 80,
                'gid' => 1,
                'title' => 'Q80',
                'type' => Question::QT_J_MAP,
                'attributes' => [],
            ],
        ]);

        (new ReflectionMethod($statistics, 'trimMeta'))->invoke($statistics, $chart);

        // No map service set: defaults to OpenStreetMap (100)
        $this->assertSame(100, $chart->getMeta()['question']['mapService']);
    }

    public function testLegacyMapIsReportedAsMapAndBrowserDetectionIsNot(): void
    {
        $statistics = new QuestionStatistics();
        (new ReflectionProperty($statistics, 'language'))->setValue($statistics, 'en');
        $legacyMap = new StatisticsChartDTO('', [], [], null, [
            'question' => [
                'qid' => 81,
                'gid' => 1,
                'title' => 'Q81',
                'type' => Question::QT_S_SHORT_FREE_TEXT,
                'question_theme_name' => 'core',
                'attributes' => ['location_mapservice' => '100'],
            ],
        ]);
        $browserDetection = new StatisticsChartDTO('', [], [], null, [
            'question' => [
                'qid' => 82,
                'gid' => 1,
                'title' => 'Q82',
                'type' => Question::QT_S_SHORT_FREE_TEXT,
                'question_theme_name' => 'browserdetect',
                'attributes' => ['location_mapservice' => '100'],
            ],
        ]);
        $trimMeta = new ReflectionMethod($statistics, 'trimMeta');

        $trimMeta->invoke($statistics, $legacyMap);
        $trimMeta->invoke($statistics, $browserDetection);

        $this->assertSame(100, $legacyMap->getMeta()['question']['mapService']);
        $this->assertSame(0, $browserDetection->getMeta()['question']['mapService']);
    }

    public function testShortTextShownAsTextareaIsNotReportedAsMap(): void
    {
        $statistics = new QuestionStatistics();
        (new ReflectionProperty($statistics, 'language'))->setValue($statistics, 'en');
        $chart = new StatisticsChartDTO('', [], [], null, [
            'question' => [
                'qid' => 83,
                'gid' => 1,
                'title' => 'Q83',
                'type' => Question::QT_S_SHORT_FREE_TEXT,
                'question_theme_name' => 'shortfreetext',
                'attributes' => ['location_mapservice' => '100', 'display_rows' => '5'],
            ],
        ]);

        (new ReflectionMethod($statistics, 'trimMeta'))->invoke($statistics, $chart);

        $this->assertSame(0, $chart->getMeta()['question']['mapService']);
    }

    private function buildPlan(
        QuestionStatistics $statistics,
        array $factories,
        ResponseAggregateBatch $batch,
        array $question
    ): array {
        $planFactoryMethod = new ReflectionMethod($statistics, 'planFactory');
        [$plan] = $planFactoryMethod->invoke(
            $statistics,
            $factories[$question['type']](),
            $batch,
            ['answers' => []],
            $question
        );

        return $plan;
    }
}
