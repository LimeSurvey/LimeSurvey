<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ArrayNumbersProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;
use Question;

class ArrayNumbersProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    /**
     * @testdox process() builds one row per scale-0 subquestion, one segment per scale-1 subquestion, with mean/stats per cell
     */
    public function testProcessBuildsRowsAndColumnSegments()
    {
        $processor = new ArrayNumbersProcessor();
        // subQuestions is keyed by qid: process() looks rows/columns back up
        // by the qid it collected per scale, not by array position.
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 6,
            'type' => Question::QT_COLON_ARRAY_NUMBERS,
            'question' => 'Rate each item',
            'subQuestions' => [
                1 => ['qid' => 1, 'scale_id' => 0, 'title' => 'R1', 'question' => 'Row 1'],
                10 => ['qid' => 10, 'scale_id' => 1, 'title' => 'C1', 'question' => 'Col 1'],
                20 => ['qid' => 20, 'scale_id' => 1, 'title' => 'C2', 'question' => 'Col 2'],
            ],
        ]);
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        $plan = $processor->process();

        $this->assertSame(['Col 1', 'Col 2'], $plan['legend']);
        $this->assertCount(1, $plan['data']);
        $row = $plan['data'][0];
        $this->assertSame('R1', $row['key']);
        $this->assertSame('Row 1', $row['title']);
        $this->assertSame(['10', '20'], array_column($row['segments'], 'key'));
        $this->assertSame(['Col 1', 'Col 2'], array_column($row['segments'], 'title'));

        $fieldWithAnswers = 'Q6_S1_S10';
        $fieldWithoutAnswers = 'Q6_S1_S20';
        $bothFields = [$fieldWithAnswers, $fieldWithoutAnswers];
        $this->injectBatchResults($batch, [
            $batch->sumValues($fieldWithAnswers) => 30,
            $batch->countNumeric($fieldWithAnswers) => 3,
            $batch->medianValue($fieldWithAnswers) => 10,
            $batch->minValue($fieldWithAnswers) => 5,
            $batch->maxValue($fieldWithAnswers) => 15,
            $batch->countNumeric($fieldWithoutAnswers) => 0,
            $batch->countAnyNonEmpty($bothFields) => 4,
        ]);

        $this->assertSame(10.0, $row['segments'][0]['value']());
        $this->assertSame([
            'mean' => 10.0,
            'median' => 10.0,
            'min' => 5.0,
            'max' => 15.0,
        ], $row['segments'][0]['stats']());

        $this->assertSame(0.0, $row['segments'][1]['value']());
        $this->assertNull($row['segments'][1]['stats']());

        $this->assertSame(4, $row['value']());
        $this->assertSame(4, $plan['total']());
    }

    /**
     * @testdox process() reports a plain zero (not a deferred value) for a row with no columns
     */
    public function testProcessReturnsZeroForRowWithoutColumns()
    {
        $processor = new ArrayNumbersProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 6,
            'type' => Question::QT_COLON_ARRAY_NUMBERS,
            'question' => 'Rate each item',
            'subQuestions' => [
                1 => ['qid' => 1, 'scale_id' => 0, 'title' => 'R1', 'question' => 'Row 1'],
            ],
        ]);
        $processor->setBatch(new ResponseAggregateBatch(1));

        $plan = $processor->process();

        $this->assertSame(0, $plan['data'][0]['value']);
        $this->assertSame(0, $plan['total']);
        $this->assertSame([], $plan['legend']);
    }
}
