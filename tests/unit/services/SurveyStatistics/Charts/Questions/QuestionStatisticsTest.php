<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ArrayTextProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\QuestionStatistics;
use PHPUnit\Framework\TestCase;
use Question;

class QuestionStatisticsTest extends TestCase
{
    /**
     * @testdox Multiple short text questions use the response-list processor
     */
    public function testMultipleShortTextUsesResponseListProcessor(): void
    {
        $statistics = new QuestionStatistics();
        $factories = (new \ReflectionProperty($statistics, 'factories'))->getValue($statistics);

        $processor = $factories[Question::QT_Q_MULTIPLE_SHORT_TEXT]();

        $this->assertInstanceOf(ArrayTextProcessor::class, $processor);
    }
}
