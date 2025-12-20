<?php

namespace ls\tests\unit\services\QuestionOrderingService;

use ls\tests\TestBaseClass;
use Question;
use LimeSurvey\Models\Services\QuestionOrderingService\SortingStrategy;

class SortingStrategyTest extends TestBaseClass
{
    /**
     * @var SortingStrategyMockSetFactory
     */
    private $mockSetFactory;

    /**
     * Set up before each test
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->mockSetFactory = new SortingStrategyMockSetFactory();
    }

    /**
     * Get a properly configured Question mock
     *
     * @return \Mockery\MockInterface|Question
     */
    protected function createQuestionMock()
    {
        $mockSet = $this->mockSetFactory->make();
        return $mockSet->question;
    }

    /**
     * @testdox determine() returns 'random' when random_order is set
     */
    public function testDetermineReturnsRandomWhenRandomOrderIsSet()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        // Setup attribute returns
        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('random_order')
            ->andReturn(1);

        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'answers');

        $this->assertEquals('random', $result);
    }

    /**
     * @testdox determine() returns 'random' when answer_order is set to 'random'
     */
    public function testDetermineReturnsRandomWhenRandomOrderIsSet2()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        // Setup attribute returns
        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('answer_order')
            ->andReturn('random');

        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'answers');

        $this->assertEquals('random', $result);
    }

    /**
     * @testdox determine() returns 'alphabetical' when answer_order is set to 'random_alphabetical' or 'alphabetical'
     */
    public function testDetermineReturnsAlphabeticalWhenAnswerOrderIsSet()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        // Setup attribute returns
        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('answer_order')
            ->andReturn('alphabetical');

        // Handle other potential calls with null
        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('random_order')
            ->andReturn(0);

        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'answers');

        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('answer_order')
            ->andReturn('random_alphabetical');
        $result2 = $strategy->determine($mockQuestion, 'answers');

        $this->assertEquals('alphabetical', $result);
        $this->assertEquals('alphabetical', $result2);
    }

    /**
     * @testdox determine() returns 'normal' when no special ordering is set or answer_order is normal or random_order = 0
     */
    public function testDetermineReturnsNormalWhenNoSpecialOrderingIsSet()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        // Setup attribute returns
        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->andReturn(null);

        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'answers');

        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('answer_order')
            ->andReturn('normal');
        $result2 = $strategy->determine($mockQuestion, 'answers');

        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('random_order')
            ->andReturn(0);
        $result3 = $strategy->determine($mockQuestion, 'answers');

        $this->assertEquals('normal', $result);
        $this->assertEquals('normal', $result2);
        $this->assertEquals('normal', $result3);
    }

    /**
     * @testdox determine() for subquestions returns 'random' when subquestion_order is random
     */
    public function testDetermineForSubquestionsReturnsRandomWhenSubquestionOrderIsRandom()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        // Setup attribute returns for subquestion_order
        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('subquestion_order')
            ->andReturn('random');

        // old random setting ist still there but 0
        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('random_order')
            ->andReturn(0);

        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'subquestions');

        $this->assertEquals('random', $result);
    }

    /**
     * @testdox determine() for subquestions returns 'random' when random_order is 1
     */
    public function testDetermineForSubquestionsReturnsRandomWhenSubquestionOrderIsRandom2()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('random_order')
            ->andReturn(1);

        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'subquestions');

        $this->assertEquals('random', $result);
    }

    /**
     * @testdox determine() for subquestions returns 'normal' when random_order is 0
     */
    public function testDetermineForSubquestionsReturnsRandomWhenSubquestionOrderIsNormal()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('random_order')
            ->andReturn(0);

        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'subquestions');

        $this->assertEquals('normal', $result);
    }

    /**
     * @testdox determine() for subquestions returns 'normal' when subquestion_order is 'normal'
     */
    public function testDetermineForSubquestionsReturnsRandomWhenSubquestionOrderIsNormal2()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('subquestion_order')
            ->andReturn('normal');

        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'subquestions');

        $this->assertEquals('normal', $result);
    }

    /**
     * @testdox determine() for subquestions returns 'alphabetical' when subquestion_order is 'random_alphabetical' or 'alphabetical'
     */
    public function testDetermineForSubquestionsReturnsAlphabeticalWhenSubquestionOrderIsAlphabetical()
    {
        // Create mock Question using Mockery
        $mockQuestion = $this->createQuestionMock();

        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('subquestion_order')
            ->andReturn('random_alphabetical');
        $strategy = new SortingStrategy();
        $result = $strategy->determine($mockQuestion, 'subquestions');

        $mockQuestion->shouldReceive('getQuestionAttribute')
            ->with('subquestion_order')
            ->andReturn('alphabetical');
        $result2 = $strategy->determine($mockQuestion, 'subquestions');

        $this->assertEquals('alphabetical', $result);
        $this->assertEquals('alphabetical', $result2);
    }
}