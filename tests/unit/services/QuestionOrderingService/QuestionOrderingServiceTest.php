<?php

namespace ls\tests\unit\services\QuestionOrderingService;

use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService;

class QuestionOrderingServiceTest extends TestBaseClass
{
    /**
     * @testdox getOrderedAnswers() returns answers in correct order
     */
    public function testGetOrderedAnswers()
    {
        // Create a simple mock Question object
        $question = \Mockery::mock(\Question::class)->makePartial();

        // Setup answers
        $answer1 = (object)['scale_id' => 0, 'sortorder' => 2];
        $answer2 = (object)['scale_id' => 0, 'sortorder' => 1];
        $answer3 = (object)['scale_id' => 1, 'sortorder' => 1];

        // Set answers directly
        $question->answers = [$answer1, $answer2, $answer3];

        // Mock necessary methods
        $question->shouldReceive('getQuestionAttribute')->andReturn(null);
        $question->shouldReceive('getQuestionType')
            ->andReturn((object)['subquestions' => 0]);

        // Create service directly
        $service = new QuestionOrderingService();

        // Call the method
        $result = $service->getOrderedAnswers($question);

        // Check structure and ordering
        $this->assertCount(2, $result);
        $this->assertCount(2, $result[0]);
        $this->assertCount(1, $result[1]);
        $this->assertEquals(1, $result[0][0]->sortorder);
        $this->assertEquals(2, $result[0][1]->sortorder);
    }

    /**
     * @testdox getOrderedSubQuestions() returns subquestions in correct order
     */
    public function testGetOrderedSubQuestions()
    {
        // Create a simple mock Question object
        $question = \Mockery::mock(\Question::class)->makePartial();

        // Setup subquestions
        $subq1 = (object)['scale_id' => 0, 'question_order' => 2, 'title' => 'SQ1'];
        $subq2 = (object)['scale_id' => 0, 'question_order' => 1, 'title' => 'SQ2'];
        $subq3 = (object)['scale_id' => 1, 'question_order' => 1, 'title' => 'SQ3'];

        // Set subquestions directly
        $question->subquestions = [$subq1, $subq2, $subq3];

        // Mock necessary methods
        $question->shouldReceive('getQuestionAttribute')->andReturn(null);

        // Mock survey property
        $question->survey = (object)[
            'language' => 'en',
            'allLanguages' => ['en']
        ];

        // Mock sid property
        $question->sid = 12345;

        // Create service directly
        $service = new QuestionOrderingService();

        // Call the method
        $result = $service->getOrderedSubQuestions($question);

        // Check structure and ordering
        $this->assertCount(2, $result);
        $this->assertCount(2, $result[0]);
        $this->assertCount(1, $result[1]);
        $this->assertEquals(1, $result[0][0]->question_order);
        $this->assertEquals(2, $result[0][1]->question_order);
    }

    /**
     * @testdox getOrderedSubQuestions() with scale_id filter returns only that scale
     */
    public function testGetOrderedSubQuestionsWithScaleFilter()
    {
        // Create a simple mock Question object
        $question = \Mockery::mock(\Question::class)->makePartial();

        // Setup subquestions
        $subq1 = (object)['scale_id' => 0, 'question_order' => 2];
        $subq2 = (object)['scale_id' => 0, 'question_order' => 1];
        $subq3 = (object)['scale_id' => 1, 'question_order' => 1];

        // Set subquestions directly
        $question->subquestions = [$subq1, $subq2, $subq3];

        // Mock necessary methods
        $question->shouldReceive('getQuestionAttribute')->andReturn(null);

        // Mock survey property for sorting
        $question->survey = (object)[
            'language' => 'en',
            'allLanguages' => ['en']
        ];

        // Mock sid property
        $question->sid = 12345;

        // Create service directly
        $service = new QuestionOrderingService();

        // Call the method with scale_id filter
        $result = $service->getOrderedSubQuestions($question, 0);

        // Check that only scale_id 0 is returned and properly sorted
        $this->assertCount(2, $result);
        // The first item should have question_order 1
        $this->assertEquals(1, $result[0]->question_order);
        // The second item should have question_order 2
        $this->assertEquals(2, $result[1]->question_order);
    }

    /**
     * @testdox getOrderedAnswers() sorts answers alphabetically with numbers appearing first
     */
    public function testGetOrderedAnswersAlphabeticallyWithNumbers()
    {
        // Create a mock Question object
        $question = \Mockery::mock(\Question::class)->makePartial();

        // Setup answers with l10n data including numeric strings
        $answer1 = (object)[
            'scale_id' => 0,
            'sortorder' => 3,
            'answerl10ns' => [
                'en' => (object)['answer' => 'Zebra']
            ]
        ];
        $answer2 = (object)[
            'scale_id' => 0,
            'sortorder' => 2,
            'answerl10ns' => [
                'en' => (object)['answer' => 'Apple']
            ]
        ];
        $answer3 = (object)[
            'scale_id' => 0,
            'sortorder' => 1,
            'answerl10ns' => [
                'en' => (object)['answer' => '10 Options']
            ]
        ];

        // Set answers directly
        $question->answers = [$answer1, $answer2, $answer3];

        // Mock necessary methods
        $question->shouldReceive('getQuestionAttribute')
            ->with('answer_order')
            ->andReturn('alphabetical');
        $question->shouldReceive('getQuestionType')
            ->andReturn((object)['subquestions' => 0]);

        // Mock survey property
        $question->survey = (object)[
            'language' => 'en',
            'allLanguages' => ['en']
        ];

        // Create service
        $service = new QuestionOrderingService();

        // Call the method
        $result = $service->getOrderedAnswers($question);

        // Check alphabetical ordering (numbers should come first, then A-Z)
        $this->assertEquals('10 Options', $result[0][0]->answerl10ns['en']->answer);
        $this->assertEquals('Apple', $result[0][1]->answerl10ns['en']->answer);
        $this->assertEquals('Zebra', $result[0][2]->answerl10ns['en']->answer);
    }

    /**
     * @testdox getOrderedSubQuestions() sorts subquestions alphabetically with numbers appearing first
     */
    public function testGetOrderedSubQuestionsAlphabeticallyWithNumbers()
    {
        // Create a mock Question object
        $question = \Mockery::mock(\Question::class)->makePartial();

        // Setup subquestions with l10n data including numeric strings
        $subq1 = (object)[
            'scale_id' => 0,
            'question_order' => 3,
            'questionl10ns' => [
                'en' => (object)['question' => 'Zebra question']
            ]
        ];
        $subq2 = (object)[
            'scale_id' => 0,
            'question_order' => 2,
            'questionl10ns' => [
                'en' => (object)['question' => 'Apple question']
            ]
        ];
        $subq3 = (object)[
            'scale_id' => 0,
            'question_order' => 1,
            'questionl10ns' => [
                'en' => (object)['question' => '1st question']
            ]
        ];

        // Set subquestions directly
        $question->subquestions = [$subq1, $subq2, $subq3];

        // Mock necessary methods
        $question->shouldReceive('getQuestionAttribute')
            ->with('subquestion_order')
            ->andReturn('alphabetical');

        // Mock survey property
        $question->survey = (object)[
            'language' => 'en',
            'allLanguages' => ['en']
        ];

        // Mock sid property
        $question->sid = 12345;

        // Create service
        $service = new QuestionOrderingService();

        // Call the method
        $result = $service->getOrderedSubQuestions($question);

        // Check alphabetical ordering (numbers should come first, then A-Z)
        $this->assertEquals('1st question', $result[0][0]->questionl10ns['en']->question);
        $this->assertEquals('Apple question', $result[0][1]->questionl10ns['en']->question);
        $this->assertEquals('Zebra question', $result[0][2]->questionl10ns['en']->question);
    }

    /**
     * @testdox getOrderedAnswers() with random sorting preserves all items
     */
    public function testGetOrderedAnswersRandomly()
    {
        // Create a mock Question object
        $question = \Mockery::mock(\Question::class)->makePartial();

        // Setup answers with unique identifiers
        $answer1 = (object)[
            'scale_id' => 0,
            'sortorder' => 1,
            'id' => 101
        ];
        $answer2 = (object)[
            'scale_id' => 0,
            'sortorder' => 2,
            'id' => 102
        ];
        $answer3 = (object)[
            'scale_id' => 0,
            'sortorder' => 3,
            'id' => 103
        ];

        // Set answers directly
        $question->answers = [$answer1, $answer2, $answer3];

        // Mock necessary methods
        $question->shouldReceive('getQuestionAttribute')
            ->with('answer_order')
            ->andReturn('random');
        $question->shouldReceive('getQuestionType')
            ->andReturn((object)['subquestions' => 0]);

        // Mock survey property
        $question->survey = (object)[
            'language' => 'en',
            'allLanguages' => ['en']
        ];

        // Create service
        $service = new QuestionOrderingService();

        // Call the method
        $result = $service->getOrderedAnswers($question);

        // Check that all items are still present (regardless of order)
        $resultIds = array_map(function($item) {
            return $item->id;
        }, $result[0]);
        sort($resultIds);

        $this->assertEquals([101, 102, 103], $resultIds);
        $this->assertCount(3, $result[0]);
    }
}
