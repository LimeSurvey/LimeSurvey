<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\NumericalProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;
use Question;

class NumericalProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    private function makeProcessor(): array
    {
        $processor = new NumericalProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 3,
            'type' => Question::QT_N_NUMERICAL,
            'title' => 'Q3',
            'question' => 'How old are you?',
        ]);
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        return [$processor, $batch];
    }

    /**
     * @testdox process() reports the answered count and descriptive stats once the batch resolves
     */
    public function testProcessReportsAnsweredCountAndStats()
    {
        [$processor, $batch] = $this->makeProcessor();

        $plan = $processor->process();

        $this->assertSame('How old are you?', $plan['title']);
        $this->assertSame([], $plan['legend']);
        $this->assertSame('Q3', $plan['data'][0]['key']);

        $countAlias = $batch->countNonEmpty('Q3', true);
        $sumAlias = $batch->sumValues('Q3', true);
        $sumSquaresAlias = $batch->sumSquares('Q3', true);
        $medianAlias = $batch->medianValue('Q3', true);
        $minAlias = $batch->minValue('Q3', true);
        $maxAlias = $batch->maxValue('Q3', true);
        $this->injectBatchResults($batch, [
            $countAlias => 4,
            $sumAlias => 40,
            $sumSquaresAlias => 450,
            $medianAlias => 10,
            $minAlias => 5,
            $maxAlias => 15,
        ]);

        $this->assertSame(4, $plan['data'][0]['value']());
        $this->assertSame(4, $plan['total']());

        $stats = $plan['data'][0]['stats']();
        $this->assertSame([
            'count' => 4,
            'sum' => 40.0,
            'standardDeviation' => 3.54,
            'mean' => 10.0,
            'min' => 5.0,
            'max' => 15.0,
            'median' => 10.0,
        ], $stats);
    }

    /**
     * @testdox process() reports null stats when nobody answered
     */
    public function testProcessReportsNullStatsWhenUnanswered()
    {
        [$processor, $batch] = $this->makeProcessor();

        $plan = $processor->process();

        $countAlias = $batch->countNonEmpty('Q3', true);
        $this->injectBatchResults($batch, [$countAlias => 0]);

        $this->assertSame(0, $plan['data'][0]['value']());
        $this->assertNull($plan['data'][0]['stats']());
    }
}
