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
     * @testdox getOrderedAnswers() sorts accented and HTML formatted answers alphabetically in the requested language
     */
    public function testGetOrderedAnswersAlphabeticallyWithAccentsAndLanguage()
    {
        $question = \Mockery::mock(\Question::class)->makePartial();

        $texts = [
            ['fr' => 'france', 'en' => 'France'],
            ['fr' => '<p>&Eacute;tats-Unis</p>', 'en' => 'United States'],
            ['fr' => 'Algérie', 'en' => 'Algeria'],
            ['fr' => 'Égypte', 'en' => 'Egypt'],
        ];
        $answers = [];
        foreach ($texts as $index => $l10n) {
            $answers[] = (object)[
                'scale_id' => 0,
                'sortorder' => $index,
                'answerl10ns' => [
                    'fr' => (object)['answer' => $l10n['fr']],
                    'en' => (object)['answer' => $l10n['en']],
                ]
            ];
        }
        $question->answers = $answers;

        $question->shouldReceive('getQuestionAttribute')
            ->with('answer_order')
            ->andReturn('alphabetical');
        $question->shouldReceive('getQuestionType')
            ->andReturn((object)['subquestions' => 0]);
        $question->survey = (object)[
            'language' => 'fr',
            'allLanguages' => ['fr', 'en']
        ];

        $service = new QuestionOrderingService();

        $result = $service->getOrderedAnswers($question, 0, 'fr');
        $this->assertSame(
            ['Algérie', 'Égypte', '<p>&Eacute;tats-Unis</p>', 'france'],
            array_map(function ($answer) {
                return $answer->answerl10ns['fr']->answer;
            }, $result)
        );

        $result = $service->getOrderedAnswers($question, 0, 'en');
        $this->assertSame(
            ['Algeria', 'Egypt', 'France', 'United States'],
            array_map(function ($answer) {
                return $answer->answerl10ns['en']->answer;
            }, $result)
        );
    }

    /**
     * @testdox getOrderedAnswers() keeps keep_codes_order codes in place when sorting alphabetically
     */
    public function testGetOrderedAnswersAlphabeticallyKeepsCodesInPlace()
    {
        $question = $this->mockQuestionWithOrderingAttributes('answer_order', 'alphabetical', 'A1;DK');
        $question->answers = $this->makeItems(
            ['A1' => 'Zebra', 'A2' => 'Mango', 'A3' => 'Apple', 'DK' => "Don't know"],
            'answers'
        );

        $result = (new QuestionOrderingService())->getOrderedAnswers($question, 0);

        $this->assertSame(['A1', 'A3', 'A2', 'DK'], array_column($result, 'code'));
    }

    /**
     * @testdox getOrderedSubQuestions() keeps keep_codes_order codes in place when sorting alphabetically
     */
    public function testGetOrderedSubQuestionsAlphabeticallyKeepsCodesInPlace()
    {
        $question = $this->mockQuestionWithOrderingAttributes('subquestion_order', 'alphabetical', 'SQ2');
        $question->subquestions = $this->makeItems(
            ['SQ1' => 'Mango', 'SQ2' => 'Zucchini', 'SQ3' => 'Banana', 'SQ4' => 'Apple'],
            'subquestions'
        );

        $result = (new QuestionOrderingService())->getOrderedSubQuestions($question, 0);

        $this->assertSame(['SQ4', 'SQ2', 'SQ3', 'SQ1'], array_column($result, 'title'));
    }

    /**
     * @testdox getOrderedAnswers() keeps keep_codes_order codes in place in both random A-Z/Z-A directions
     */
    public function testGetOrderedAnswersRandomAlphabeticallyKeepsCodesInPlace()
    {
        $question = $this->mockQuestionWithOrderingAttributes('answer_order', 'random_alphabetical', 'DK');
        $question->answers = $this->makeItems(
            ['A1' => 'Mango', 'A2' => 'Apple', 'A3' => 'Zebra', 'DK' => "Don't know"],
            'answers'
        );

        $service = new QuestionOrderingService();
        $seen = [];
        for ($i = 0; $i < 50; $i++) {
            $codes = array_column($service->getOrderedAnswers($question, 0), 'code');
            $this->assertContains($codes, [['A2', 'A1', 'A3', 'DK'], ['A3', 'A1', 'A2', 'DK']]);
            $seen[implode(';', $codes)] = true;
        }
        $this->assertCount(2, $seen, 'Both sort directions should occur');
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

    /**
     * Create a partial Question mock with the given order attribute and keep_codes_order
     *
     * @param string $orderAttribute 'answer_order' or 'subquestion_order'
     * @param string $orderValue Value of the order attribute
     * @param string|null $keepCodesOrder Value of keep_codes_order
     * @return \Question
     */
    private function mockQuestionWithOrderingAttributes(string $orderAttribute, string $orderValue, $keepCodesOrder)
    {
        $question = \Mockery::mock(\Question::class)->makePartial();
        $question->shouldReceive('getQuestionAttribute')->with($orderAttribute)->andReturn($orderValue);
        $question->shouldReceive('getQuestionAttribute')->with('keep_codes_order')->andReturn($keepCodesOrder);
        $question->shouldReceive('getQuestionAttribute')->andReturn(null);
        $question->survey = (object)[
            'language' => 'en',
            'allLanguages' => ['en']
        ];
        $question->sid = 12345;
        return $question;
    }

    /**
     * Create answer or subquestion stubs in the given (database) order
     *
     * @param array<string, string> $texts Item texts indexed by code
     * @param string $context 'answers' or 'subquestions'
     * @return object[]
     */
    private function makeItems(array $texts, string $context): array
    {
        $items = [];
        $order = 1;
        foreach ($texts as $code => $text) {
            if ($context === 'answers') {
                $items[] = (object)[
                    'scale_id' => 0,
                    'sortorder' => $order++,
                    'code' => $code,
                    'answerl10ns' => ['en' => (object)['answer' => $text]]
                ];
            } else {
                $items[] = (object)[
                    'scale_id' => 0,
                    'question_order' => $order++,
                    'title' => $code,
                    'questionl10ns' => ['en' => (object)['question' => $text]]
                ];
            }
        }
        return $items;
    }
}
