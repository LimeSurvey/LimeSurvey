<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ArrayTextProcessor;
use PHPUnit\Framework\TestCase;
use Question;

class ArrayTextProcessorTest extends TestCase
{
    /**
     * @testdox process() returns an empty chart plan carrying only the question title
     */
    public function testProcessReturnsEmptyPlan()
    {
        $processor = new ArrayTextProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 11,
            'type' => Question::QT_SEMICOLON_ARRAY_TEXT,
            'question' => 'Comments grid',
        ]);

        $plan = $processor->process();

        $this->assertSame([
            'title' => 'Comments grid',
            'legend' => [],
            'data' => [],
        ], $plan);
    }
}
