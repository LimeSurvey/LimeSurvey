<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\TextProcessor;
use PHPUnit\Framework\TestCase;
use Question;

class TextProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    /**
     * @testdox process() reports Answer/NoAnswer counts derived from total and answered responses
     */
    public function testProcessReportsAnswerAndNoAnswerCounts()
    {
        $processor = new TextProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 7,
            'type' => Question::QT_S_SHORT_FREE_TEXT,
            'question' => 'What do you think?',
        ]);
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        $plan = $processor->process();

        $this->assertSame('What do you think?', $plan['title']);
        $this->assertSame(['Answer', 'NoAnswer'], $plan['legend']);
        $this->assertSame('Answer', $plan['data'][0]['key']);
        $this->assertSame('NoAnswer', $plan['data'][1]['key']);

        $totalAlias = $batch->countTotal();
        $answeredAlias = $batch->countNonEmpty('Q7');
        $this->injectBatchResults($batch, [$totalAlias => 10, $answeredAlias => 6]);

        $this->assertSame(6, $plan['data'][0]['value']());
        $this->assertSame(4, $plan['data'][1]['value']());
    }
}
