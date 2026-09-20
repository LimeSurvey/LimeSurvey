<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\MultipleNumericalProcessor;
use PHPUnit\Framework\TestCase;
use Question;

class MultipleNumericalProcessorTest extends TestCase
{
    /**
     * @testdox process() returns an empty chart plan carrying only the question title
     */
    public function testProcessReturnsEmptyPlan()
    {
        $processor = new MultipleNumericalProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 9,
            'title' => 'Q9',
            'type' => Question::QT_K_MULTIPLE_NUMERICAL,
            'question' => 'Enter numbers',
        ]);

        $plan = $processor->process();

        $this->assertSame([
            'title' => 'Enter numbers',
            'legend' => [],
            'data' => [],
        ], $plan);
    }
}
