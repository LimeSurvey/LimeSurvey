<?php

namespace ls\tests\unit\services\QuestionOrderingService;

use Mockery;
use Question;
use LimeSurvey\Models\Services\QuestionOrderingService\SortingStrategy;
use LimeSurvey\Models\Services\QuestionOrderingService\RandomizerHelper;

class QuestionOrderingMockSetFactory
{
    /**
     * Create a mock set for QuestionOrderingService tests
     *
     * @param object|null $init Initial values
     * @return object Mock set
     */
    public function make($init = null)
    {
        $mockSet = new \stdClass();

        // Create mock Question
        $mockSet->question = Mockery::mock(Question::class)->makePartial();

        // Set up common method expectations
        $mockSet->question->shouldReceive('getQuestionAttribute')
            ->andReturn(null);

        // Mock getMetaData to prevent CActiveRecord errors
        $mockSet->question->shouldReceive('getMetaData')
            ->andReturn((object)[
                'tableSchema' => (object)['primaryKey' => 'qid'],
                'columns' => ['qid' => null]
            ]);

        // Mock survey property
        $mockSet->question->survey = (object)[
            'language' => 'en',
            'allLanguages' => ['en']
        ];

        // Mock sid property
        $mockSet->question->sid = 12345;

        // Create mock SortingStrategy
        $mockSet->sortingStrategy = Mockery::mock(SortingStrategy::class);
        $mockSet->sortingStrategy->shouldReceive('determine')
            ->andReturn('normal');

        return $mockSet;
    }
}
