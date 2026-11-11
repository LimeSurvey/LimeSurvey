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

    /**
     * @dataProvider mapServiceProvider
     */
    public function testMapServiceMetadata(array $question, int $expected): void
    {
        $statistics = new QuestionStatistics();
        (new ReflectionProperty($statistics, 'language'))->setValue($statistics, 'en');
        $chart = new StatisticsChartDTO('', [], [], null, [
            'question' => $question + ['qid' => 80, 'gid' => 1, 'title' => 'Q80'],
        ]);

        (new ReflectionMethod($statistics, 'trimMeta'))->invoke($statistics, $chart);

        $this->assertSame($expected, $chart->getMeta()['question']['mapService']);
    }

    public static function mapServiceProvider(): array
    {
        $shortText = Question::QT_S_SHORT_FREE_TEXT;
        return [
            'Map without a service falls back to OpenStreetMap' => [
                ['type' => Question::QT_J_MAP, 'attributes' => []],
                100,
            ],
            'Map with Google' => [
                ['type' => Question::QT_J_MAP, 'attributes' => ['location_mapservice' => '1']],
                1,
            ],
            'Map with OpenStreetMap' => [
                ['type' => Question::QT_J_MAP, 'attributes' => ['location_mapservice' => '100']],
                100,
            ],
            'Short text with Google (old map question)' => [
                [
                    'type' => $shortText,
                    'question_theme_name' => 'core',
                    'attributes' => ['location_mapservice' => '1'],
                ],
                1,
            ],
            'Short text with OpenStreetMap (old map question)' => [
                [
                    'type' => $shortText,
                    'question_theme_name' => 'shortfreetext',
                    'attributes' => ['location_mapservice' => '100'],
                ],
                100,
            ],
            'Short text without a map service' => [
                ['type' => $shortText, 'question_theme_name' => 'shortfreetext', 'attributes' => []],
                0,
            ],
            'Short text with a legacy map service is not a map' => [
                ['type' => $shortText, 'question_theme_name' => 'shortfreetext', 'attributes' => ['location_mapservice' => '2']],
                0,
            ],
            'Map with an unknown map service falls back to OpenStreetMap' => [
                ['type' => Question::QT_J_MAP, 'attributes' => ['location_mapservice' => '2']],
                100,
            ],
            'Browser detection is not a map' => [
                [
                    'type' => $shortText,
                    'question_theme_name' => 'browserdetect',
                    'attributes' => ['location_mapservice' => '100'],
                ],
                0,
            ],
            'Short text shown as a textarea is not a map' => [
                [
                    'type' => $shortText,
                    'question_theme_name' => 'shortfreetext',
                    'attributes' => ['location_mapservice' => '100', 'display_rows' => '5'],
                ],
                0,
            ],
            'Other question types are not maps' => [
                [
                    'type' => Question::QT_N_NUMERICAL,
                    'attributes' => ['location_mapservice' => '100'],
                ],
                0,
            ],
        ];
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
