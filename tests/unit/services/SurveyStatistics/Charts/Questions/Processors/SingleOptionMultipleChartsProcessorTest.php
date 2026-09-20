<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\SingleOptionMultipleChartsProcessor;
use PHPUnit\Framework\TestCase;
use Question;

class SingleOptionMultipleChartsProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    /**
     * @testdox process() builds a stacked Yes/Uncertain/No/NoAnswer chart per subquestion for that array type
     */
    public function testProcessBuildsFixedCodeStackedChart()
    {
        $processor = new SingleOptionMultipleChartsProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 8,
            'title' => 'Q8',
            'type' => Question::QT_C_ARRAY_YES_UNCERTAIN_NO,
            'question' => 'Rate these',
            'subQuestions' => [
                1 => ['qid' => 1, 'scale_id' => 0, 'title' => 'SQ1', 'question' => 'Sub 1'],
            ],
        ]);
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        $plan = $processor->process();

        $this->assertSame(['Yes', 'Uncertain', 'No', 'No answer'], $plan['legend']);
        $this->assertCount(1, $plan['data']);
        $this->assertSame('SQ1', $plan['data'][0]['key']);
        $this->assertSame(['Y', 'U', 'N', 'NoAnswer'], array_column($plan['data'][0]['segments'], 'key'));

        $field = 'Q8_S1';
        $this->injectBatchResults($batch, [
            $batch->countValue($field, 'Y') => 4,
            $batch->countValue($field, 'U') => 2,
            $batch->countValue($field, 'N') => 1,
            $batch->countBlank($field) => 3,
        ]);

        $this->assertSame([4, 2, 1, 3], $this->resolveAll(array_column($plan['data'][0]['segments'], 'value')));
    }

    /**
     * @testdox process() builds a stacked chart from the question's own answer codes for the free-answer-scale array type
     */
    public function testProcessUsesQuestionAnswersForAnswerScaleType()
    {
        $processor = new SingleOptionMultipleChartsProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 8,
            'title' => 'Q8',
            'type' => Question::QT_F_ARRAY,
            'question' => 'Rate these',
            'subQuestions' => [
                1 => ['qid' => 1, 'scale_id' => 0, 'title' => 'SQ1', 'question' => 'Sub 1'],
            ],
        ]);
        $processor->setAnswers([
            ['scale_id' => 0, 'code' => 'A1', 'answer' => 'Option 1'],
            ['scale_id' => 0, 'code' => 'A2', 'answer' => 'Option 2'],
            // a scale-1 answer must be ignored for this (single-scale) array type
            ['scale_id' => 1, 'code' => 'B1', 'answer' => 'Other scale'],
        ]);
        $processor->setBatch(new ResponseAggregateBatch(1));

        $plan = $processor->process();

        // QT_F_ARRAY is in the "always has a NoAnswer bucket" set.
        $this->assertSame(['A1', 'A2', 'NoAnswer'], array_column($plan['data'][0]['segments'], 'key'));
        $this->assertSame(['Option 1', 'Option 2', 'No answer'], array_column($plan['data'][0]['segments'], 'title'));
    }

    /**
     * @testdox process() returns an empty plan for question types it does not handle
     */
    public function testProcessReturnsEmptyArrayForUnhandledType()
    {
        $processor = new SingleOptionMultipleChartsProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 8,
            'title' => 'Q8',
            'type' => Question::QT_L_LIST,
            'question' => 'Not an array type',
        ]);
        $processor->setBatch(new ResponseAggregateBatch(1));

        $this->assertSame([], $processor->process());
    }
}
