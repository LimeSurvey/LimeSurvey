<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\MultipleChoiceProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;
use Question;

class MultipleChoiceProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    private function baseQuestion(string $other): array
    {
        return [
            'sid' => 1,
            'gid' => 1,
            'qid' => 5,
            'title' => 'Q5',
            'type' => Question::QT_M_MULTIPLE_CHOICE,
            'question' => 'Pick some',
            'other' => $other,
            'subQuestions' => [
                ['qid' => 1, 'title' => 'SQ1', 'question' => 'Sub 1'],
                ['qid' => 2, 'title' => 'SQ2', 'question' => 'Sub 2'],
            ],
        ];
    }

    /**
     * @testdox process() adds one data item per subquestion plus an "other" item when enabled
     */
    public function testProcessAddsOtherItemWhenEnabled()
    {
        $processor = new MultipleChoiceProcessor();
        $processor->setQuestion($this->baseQuestion(Question::QT_Y_YES_NO_RADIO));
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        $plan = $processor->process();

        $this->assertSame(['Sub 1', 'Sub 2', 'other'], $plan['legend']);
        $this->assertSame(['SQ1', 'SQ2', 'other'], array_column($plan['data'], 'key'));
        $this->assertSame(['Q5_S1', 'Q5_S2', 'Q5_Cother'], array_column($plan['data'], 'field'));

        $alias1 = $batch->countNonEmpty('Q5_S1');
        $alias2 = $batch->countNonEmpty('Q5_S2');
        $aliasOther = $batch->countNonEmpty('Q5_Cother');
        $this->injectBatchResults($batch, [$alias1 => 3, $alias2 => 1, $aliasOther => 2]);

        $this->assertSame([3, 1, 2], $this->resolveAll(array_column($plan['data'], 'value')));
    }

    /**
     * @testdox process() omits the "other" item when the question has no "other" option
     */
    public function testProcessOmitsOtherItemWhenDisabled()
    {
        $processor = new MultipleChoiceProcessor();
        $processor->setQuestion($this->baseQuestion('N'));
        $processor->setBatch(new ResponseAggregateBatch(1));

        $plan = $processor->process();

        $this->assertSame(['Sub 1', 'Sub 2'], $plan['legend']);
        $this->assertSame(['SQ1', 'SQ2'], array_column($plan['data'], 'key'));
    }
}
