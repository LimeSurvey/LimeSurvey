<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\RankingProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;
use Question;

class RankingProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    /**
     * @testdox process() registers one JSON-position aggregate per rank per option, and totals them for the bar value
     */
    public function testProcessSumsRanksIntoTotalPerOption()
    {
        $processor = new RankingProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 12,
            'type' => Question::QT_R_RANKING,
            'question' => 'Rank these',
            'subQuestions' => [
                ['title' => 'Opt1', 'question' => 'Option 1'],
                ['title' => 'Opt2', 'question' => 'Option 2'],
            ],
        ]);
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        $plan = $processor->process();

        $this->assertSame(['Option 1', 'Option 2'], $plan['legend']);
        $this->assertSame(['Opt1', 'Opt2'], array_column($plan['data'], 'key'));
        // 2 subquestions -> ranks 1 and 2 are both possible.
        $this->assertSame([1, 2], array_column($plan['data'][0]['ranks'], 'position'));

        $column = 'Q12';
        $this->injectBatchResults($batch, [
            $batch->countJsonArrayValue($column, 0, 'Opt1') => 5,
            $batch->countJsonArrayValue($column, 1, 'Opt1') => 2,
            $batch->countJsonArrayValue($column, 0, 'Opt2') => 1,
            $batch->countJsonArrayValue($column, 1, 'Opt2') => 3,
        ]);

        $this->assertSame(7, $plan['data'][0]['value']());
        $this->assertSame(4, $plan['data'][1]['value']());
        $this->assertSame([5, 2], $this->resolveAll(array_column($plan['data'][0]['ranks'], 'value')));
    }

    /**
     * @testdox process() caps the ranks registered per option at max_subquestions when it is set and smaller
     */
    public function testProcessCapsRanksAtMaxSubquestions()
    {
        $processor = new RankingProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 12,
            'type' => Question::QT_R_RANKING,
            'question' => 'Rank these',
            'attributes' => ['max_subquestions' => 1],
            'subQuestions' => [
                ['title' => 'Opt1', 'question' => 'Option 1'],
                ['title' => 'Opt2', 'question' => 'Option 2'],
            ],
        ]);
        $processor->setBatch(new ResponseAggregateBatch(1));

        $plan = $processor->process();

        $this->assertSame([1], array_column($plan['data'][0]['ranks'], 'position'));
        $this->assertSame([1], array_column($plan['data'][1]['ranks'], 'position'));
    }
}
