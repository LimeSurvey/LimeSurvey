<?php

namespace ls\tests\unit\services\QuestionOrderingService;

use Mockery;
use Question;

class SortingStrategyMockSetFactory
{
    /**
     * Create a mock set for SortingStrategy tests
     *
     * @return \stdClass Object containing mock objects
     */
    public function make()
    {
        $mockSet = new \stdClass();

        // Create mock Question
        $mockSet->question = Mockery::mock(Question::class);

        // Set up common method expectations
        $mockSet->question->shouldReceive('getQuestionAttribute')
            ->byDefault()
            ->andReturn(null);

        // Allow setAttribute method to be called
        $mockSet->question->shouldReceive('setAttribute')
            ->byDefault()
            ->andReturn(null);

        // Set survey property directly
        $mockSet->question->survey = (object)[
            'language' => 'en',
            'allLanguages' => ['en']
        ];

        // Set sid property
        $mockSet->question->sid = 12345;

        return $mockSet;
    }
}
