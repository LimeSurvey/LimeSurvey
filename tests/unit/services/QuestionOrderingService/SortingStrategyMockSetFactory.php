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

        // Create mock Question with makePartial to allow real methods
        $mockSet->question = Mockery::mock(Question::class)->makePartial();

        // Set up common method expectations
        $mockSet->question->shouldReceive('getQuestionAttribute')
            ->byDefault()
            ->andReturn(null);

        // Mock getAttribute to return type
        $mockSet->question->shouldReceive('getAttribute')
            ->with('type')
            ->andReturn(Question::QT_L_LIST);

        // Mock hasAttribute to return true for type
        $mockSet->question->shouldReceive('hasAttribute')
            ->with('type')
            ->andReturn(true);

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