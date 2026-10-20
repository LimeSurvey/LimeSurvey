<?php

namespace ls\tests\unit\services\QuestionOrderingService;

use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\QuestionOrderingService\RandomizerHelper;
use Mockery;
use Question;

class RandomizerHelperTest extends TestBaseClass
{
    /** @var RandomizerHelperMockSetFactory */
    private $mockSetFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockSetFactory = new RandomizerHelperMockSetFactory();
    }

    /**
     * @testdox applyRandomSorting() preserves the structure of grouped items
     */
    public function testApplyRandomSortingPreservesStructure()
    {
        // Create test data with multiple scales
        $item1 = (object)['id' => 1, 'scale_id' => 0];
        $item2 = (object)['id' => 2, 'scale_id' => 0];
        $item3 = (object)['id' => 3, 'scale_id' => 0];
        $item4 = (object)['id' => 4, 'scale_id' => 1];
        $item5 = (object)['id' => 5, 'scale_id' => 1];

        $groupedItems = [
            0 => [$item1, $item2, $item3],
            1 => [$item4, $item5]
        ];

        // Get mock question
        $mockSet = $this->mockSetFactory->make();

        $helper = new RandomizerHelper();
        $result = $helper->applyRandomSorting($groupedItems, $mockSet->question, 'answers');

        // Check structure is preserved
        $this->assertCount(2, $result);
        $this->assertCount(3, $result[0]);
        $this->assertCount(2, $result[1]);

        // Check all items are still present
        $allIds = [];
        foreach ($result as $scaleItems) {
            foreach ($scaleItems as $item) {
                $allIds[] = $item->id;
            }
        }
        sort($allIds);
        $this->assertEquals([1, 2, 3, 4, 5], $allIds);
    }

    /**
     * @testdox applyRandomSortingToSubquestions() randomizes the exclusive option when it is not in keep_codes_order
     */
    public function testApplyRandomSortingToSubquestionsRandomizesExclusiveWithoutKeepCodes()
    {
        $groupedSubquestions = $this->makeSubquestions(['SQ001', 'SQ002', 'SQ003', 'SQ004']);
        $question = $this->mockQuestionWithOrderingAttributes('SQ004', null);
        $mockSet = $this->mockSetFactory->make();

        $helper = new RandomizerHelper();
        $positions = [];
        for ($run = 0; $run < 50; $run++) {
            $result = $helper->applyRandomSortingToSubquestions($groupedSubquestions, $question, $mockSet->survey);

            $titles = $this->getTitles($result[0]);
            $sortedTitles = $titles;
            sort($sortedTitles);
            $this->assertEquals(['SQ001', 'SQ002', 'SQ003', 'SQ004'], $sortedTitles);

            $positions[] = array_search('SQ004', $titles, true);
        }

        // With 50 random runs, the exclusive option must not always land at its original position
        $this->assertGreaterThan(1, count(array_unique($positions)));
    }

    /**
     * @testdox applyRandomSortingToSubquestions() keeps the exclusive option at its original position when it is in keep_codes_order
     */
    public function testApplyRandomSortingToSubquestionsKeepsExclusiveWithKeepCodes()
    {
        // question_order deliberately starts at 0 to make sure positions don't depend on it
        $groupedSubquestions = $this->makeSubquestions(['SQ001', 'SQ002', 'SQ003', 'SQ004', 'SQ005', 'SQ006'], 0);
        $question = $this->mockQuestionWithOrderingAttributes('SQ004', 'SQ004');
        $mockSet = $this->mockSetFactory->make();

        $helper = new RandomizerHelper();
        $orders = [];
        for ($run = 0; $run < 50; $run++) {
            $result = $helper->applyRandomSortingToSubquestions($groupedSubquestions, $question, $mockSet->survey);

            $titles = $this->getTitles($result[0]);
            $this->assertCount(6, $titles);
            $this->assertEquals('SQ004', $titles[3]);
            $orders[] = implode(',', $titles);
        }

        // The other subquestions must still be randomized
        $this->assertGreaterThan(1, count(array_unique($orders)));
    }

    /**
     * @testdox applyRandomSortingToSubquestions() works correctly without excluded items
     */
    public function testApplyRandomSortingToSubquestionsWithoutExcludedItem()
    {
        // Create test data
        $subq1 = (object)['scale_id' => 0, 'title' => 'SQ1', 'question_order' => 1];
        $subq2 = (object)['scale_id' => 0, 'title' => 'SQ2', 'question_order' => 2];
        $subq3 = (object)['scale_id' => 0, 'title' => 'SQ3', 'question_order' => 3];

        $groupedSubquestions = [
            0 => [$subq1, $subq2, $subq3]
        ];

        // Get mock question
        $mockSet = $this->mockSetFactory->make();

        $helper = new RandomizerHelper();
        $result = $helper->applyRandomSortingToSubquestions($groupedSubquestions, $mockSet->question, $mockSet->survey);

        // Check structure
        $this->assertCount(1, $result);
        $this->assertCount(3, $result[0]);

        // Check all items are still present
        $allTitles = [];
        foreach ($result[0] as $item) {
            $allTitles[] = $item->title;
        }
        sort($allTitles);
        $this->assertEquals(['SQ1', 'SQ2', 'SQ3'], $allTitles);
    }

    /**
     * @testdox applyRandomSortingToSubquestions() handles multiple scale groups correctly
     */
    public function testApplyRandomSortingToSubquestionsWithMultipleScales()
    {
        // Create test data with multiple scales
        $subq1 = (object)['scale_id' => 0, 'title' => 'SQ1', 'question_order' => 1];
        $subq2 = (object)['scale_id' => 0, 'title' => 'SQ2', 'question_order' => 2];
        $subq3 = (object)['scale_id' => 1, 'title' => 'SQ3', 'question_order' => 1];
        $subq4 = (object)['scale_id' => 1, 'title' => 'SQ4', 'question_order' => 2];

        $groupedSubquestions = [
            0 => [$subq1, $subq2],
            1 => [$subq3, $subq4]
        ];

        // Get mock question
        $mockSet = $this->mockSetFactory->make();

        $helper = new RandomizerHelper();
        $result = $helper->applyRandomSortingToSubquestions($groupedSubquestions, $mockSet->question, $mockSet->survey);

        // Check structure is preserved
        $this->assertCount(2, $result);
        $this->assertCount(2, $result[0]);
        $this->assertCount(2, $result[1]);

        // Check all items are still present in their respective scales
        $scale0Titles = [];
        foreach ($result[0] as $item) {
            $scale0Titles[] = $item->title;
        }
        sort($scale0Titles);
        $this->assertEquals(['SQ1', 'SQ2'], $scale0Titles);

        $scale1Titles = [];
        foreach ($result[1] as $item) {
            $scale1Titles[] = $item->title;
        }
        sort($scale1Titles);
        $this->assertEquals(['SQ3', 'SQ4'], $scale1Titles);
    }

    /**
     * @testdox applyRandomSorting() for answers keeps specified codes at original positions
     */
    public function testApplyRandomSortingForAnswersKeepsCodesInPlace()
    {
        // Answers in original DB order: A, B, C, D, E
        $item1 = (object)['id' => 1, 'scale_id' => 0, 'code' => 'A'];
        $item2 = (object)['id' => 2, 'scale_id' => 0, 'code' => 'B'];
        $item3 = (object)['id' => 3, 'scale_id' => 0, 'code' => 'C'];
        $item4 = (object)['id' => 4, 'scale_id' => 0, 'code' => 'D'];
        $item5 = (object)['id' => 5, 'scale_id' => 0, 'code' => 'E'];

        $groupedItems = [
            0 => [$item1, $item2, $item3, $item4, $item5]
        ];

        // Mock question with keep_codes_order configured
        $question = Mockery::mock(Question::class)->makePartial();
        $question->shouldReceive('getQuestionAttribute')
            ->with('keep_codes_order')
            ->andReturn('A;C');

        $helper = new RandomizerHelper();
        $result = $helper->applyRandomSorting($groupedItems, $question, 'answers');

        // Structure and contents preserved
        $this->assertCount(1, $result);
        $this->assertCount(5, $result[0]);
        $codes = array_map(function ($item) {
            return $item->code;
        }, $result[0]);
        sort($codes);
        $this->assertEquals(['A', 'B', 'C', 'D', 'E'], $codes);

        // A and C must keep their original positions (indexes 0 and 2)
        $this->assertEquals('A', $result[0][0]->code);
        $this->assertEquals('C', $result[0][2]->code);
    }

    /**
     * @testdox applyRandomSortingToSubquestions() keeps specified codes at original positions
     */
    public function testApplyRandomSortingToSubquestionsKeepsCodesInPlace()
    {
        // Subquestions in original DB order: SQ1, SQ2, SQ3, SQ4
        $subq1 = (object)['scale_id' => 0, 'title' => 'SQ1', 'question_order' => 1];
        $subq2 = (object)['scale_id' => 0, 'title' => 'SQ2', 'question_order' => 2];
        $subq3 = (object)['scale_id' => 0, 'title' => 'SQ3', 'question_order' => 3];
        $subq4 = (object)['scale_id' => 0, 'title' => 'SQ4', 'question_order' => 4];

        $groupedSubquestions = [
            0 => [$subq1, $subq2, $subq3, $subq4]
        ];

        // Mock question with keep_codes_order for subquestions
        $question = Mockery::mock(Question::class)->makePartial();
        $question->sid = 12345;
        $question->shouldReceive('getQuestionAttribute')
            ->with('exclude_all_others')
            ->andReturn(null);
        $question->shouldReceive('getQuestionAttribute')
            ->with('keep_codes_order')
            ->andReturn('SQ1;SQ4');

        // Mock survey required for initialize()
        $mockSet = $this->mockSetFactory->make();

        $helper = new RandomizerHelper();
        $result = $helper->applyRandomSortingToSubquestions(
            $groupedSubquestions,
            $question,
            $mockSet->survey
        );

        // Structure and contents preserved
        $this->assertCount(1, $result);
        $this->assertCount(4, $result[0]);

        $titles = array_map(function ($item) {
            return $item->title;
        }, $result[0]);
        $sortedTitles = $titles;
        sort($sortedTitles);
        $this->assertEquals(['SQ1', 'SQ2', 'SQ3', 'SQ4'], $sortedTitles);

        // SQ1 and SQ4 must keep their original positions (indexes 0 and 3)
        $this->assertEquals('SQ1', $result[0][0]->title);
        $this->assertEquals('SQ4', $result[0][3]->title);
    }

    /**
     * @testdox applyRandomSortingToSubquestions() keeps exclude_all_others and keep_codes_order consistent when they overlap
     */
    public function testApplyRandomSortingToSubquestionsWithExcludeAllOthersAndKeepCodes()
    {
        // Subquestions in original DB order: SQ1, EXCL, SQ_PIN, SQ4
        $subq1 = (object)['scale_id' => 0, 'title' => 'SQ1', 'question_order' => 1];
        $subq2 = (object)['scale_id' => 0, 'title' => 'EXCL', 'question_order' => 2];
        $subq3 = (object)['scale_id' => 0, 'title' => 'SQ_PIN', 'question_order' => 3];
        $subq4 = (object)['scale_id' => 0, 'title' => 'SQ4', 'question_order' => 4];

        $groupedSubquestions = [
            0 => [$subq1, $subq2, $subq3, $subq4]
        ];

        // Mock question with both exclude_all_others and keep_codes_order pointing at EXCL,
        // and keep_codes_order also pinning SQ_PIN.
        $question = Mockery::mock(Question::class)->makePartial();
        $question->sid = 12345;
        $question->shouldReceive('getQuestionAttribute')
            ->with('exclude_all_others')
            ->andReturn('EXCL');
        $question->shouldReceive('getQuestionAttribute')
            ->with('random_order')
            ->andReturn(1);
        $question->shouldReceive('getQuestionAttribute')
            ->with('subquestion_order')
            ->andReturn(null);
        $question->shouldReceive('getQuestionAttribute')
            ->with('keep_codes_order')
            ->andReturn('EXCL;SQ_PIN');

        // Mock survey required for initialize()
        $mockSet = $this->mockSetFactory->make();

        $helper = new RandomizerHelper();
        $result = $helper->applyRandomSortingToSubquestions(
            $groupedSubquestions,
            $question,
            $mockSet->survey
        );

        // Structure preserved
        $this->assertCount(1, $result);
        $this->assertCount(4, $result[0]);

        $titles = array_map(function ($item) {
            return $item->title;
        }, $result[0]);
        $sortedTitles = $titles;
        sort($sortedTitles);
        $this->assertEquals(['EXCL', 'SQ1', 'SQ4', 'SQ_PIN'], $sortedTitles);

        // EXCL keeps its original position (index 1) because it is listed in keep_codes_order
        $this->assertEquals('EXCL', $result[0][1]->title);
        // SQ_PIN should keep its original DB position (index 2)
        $this->assertEquals('SQ_PIN', $result[0][2]->title);
    }

    /**
     * Build subquestions of scale 0 in the given order
     *
     * @param string[] $titles
     * @param int $firstOrder question_order of the first subquestion
     * @return array
     */
    private function makeSubquestions(array $titles, int $firstOrder = 1): array
    {
        $subquestions = [];
        foreach ($titles as $index => $title) {
            $subquestions[] = (object)['scale_id' => 0, 'title' => $title, 'question_order' => $firstOrder + $index];
        }
        return [0 => $subquestions];
    }

    /**
     * Mock a question with random subquestion order and the given exclusive option / keep codes
     *
     * @param string|null $excludeAllOthers
     * @param string|null $keepCodesOrder
     * @return Question
     */
    private function mockQuestionWithOrderingAttributes($excludeAllOthers, $keepCodesOrder): Question
    {
        $question = Mockery::mock(Question::class)->makePartial();
        $question->sid = 12345;
        $question->shouldReceive('getQuestionAttribute')->with('exclude_all_others')->andReturn($excludeAllOthers);
        $question->shouldReceive('getQuestionAttribute')->with('keep_codes_order')->andReturn($keepCodesOrder);
        $question->shouldReceive('getQuestionAttribute')->with('subquestion_order')->andReturn('random');
        $question->shouldReceive('getQuestionAttribute')->with('random_order')->andReturn(null);
        return $question;
    }

    /**
     * @param array $subquestions
     * @return string[]
     */
    private function getTitles(array $subquestions): array
    {
        return array_map(function ($item) {
            return $item->title;
        }, $subquestions);
    }
}
