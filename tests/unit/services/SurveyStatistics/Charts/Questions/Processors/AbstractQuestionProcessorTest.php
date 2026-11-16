<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\AbstractQuestionProcessor;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use PHPUnit\Framework\TestCase;
use Question;

/**
 * Minimal concrete processor exposing the protected helpers so the abstract
 * base class's own logic (shared by every real processor) can be tested
 * directly, without going through any one subclass's process() shape.
 */
class ConcreteTestQuestionProcessor extends AbstractQuestionProcessor
{
    public function process()
    {
        return [];
    }

    public function publicBuildItemsFromCodes(string $fieldname, array $codes, array $labels = []): array
    {
        return $this->buildItemsFromCodes($fieldname, $codes, $labels);
    }

    public function publicBatchGetResponseCounts(array $fieldNames): array
    {
        return $this->batchGetResponseCounts($fieldNames);
    }
}

class AbstractQuestionProcessorTest extends TestCase
{
    use ResponseAggregateBatchTestHelper;

    /**
     * @testdox setQuestion() rejects data missing sid/gid/qid
     */
    public function testSetQuestionRejectsIncompleteData()
    {
        $processor = new ConcreteTestQuestionProcessor();

        $this->expectException(InvalidArgumentException::class);
        $processor->setQuestion(['sid' => 1, 'gid' => 2]);
    }

    /**
     * @testdox setQuestion() flattens the title and builds rt() as "Q<qid>"
     */
    public function testSetQuestionBuildsRtAndFlattensTitle()
    {
        $processor = new ConcreteTestQuestionProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 2,
            'qid' => 42,
            'type' => Question::QT_L_LIST,
            'title' => '<span>Q1</span>',
        ]);

        $question = $processor->getQuestion();
        $this->assertSame(1, $question['sid']);
        $this->assertSame('Q1', $question['title']);
    }

    /**
     * @testdox getTotalCount()/countFieldResponses() defer to the batch and resolve after results are known
     */
    public function testGetTotalCountAndCountFieldResponsesAreDeferred()
    {
        $processor = new ConcreteTestQuestionProcessor();
        $processor->setQuestion(['sid' => 1, 'gid' => 1, 'qid' => 1, 'type' => Question::QT_L_LIST, 'title' => 'Q1']);

        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        $total = $processor->getTotalCount();
        $answered = $this->callProtected($processor, 'countFieldResponses', ['Q1']);

        $this->assertIsCallable($total);
        $this->assertIsCallable($answered);

        $totalAlias = $batch->countTotal();
        $answeredAlias = $batch->countNonEmpty('Q1');
        $this->injectBatchResults($batch, [$totalAlias => 10, $answeredAlias => 7]);

        $this->assertSame(10, $total());
        $this->assertSame(7, $answered());
    }

    /**
     * @testdox buildItemsFromCodes() pairs codes with labels and adds a NoAnswer bucket for no-answer-type questions
     */
    public function testBuildItemsFromCodesAddsNoAnswerForApplicableTypes()
    {
        $processor = new ConcreteTestQuestionProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 1,
            'type' => Question::QT_G_GENDER,
            'title' => 'Q1',
        ]);
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        [$legend, $items] = $processor->publicBuildItemsFromCodes('Q1', ['F', 'M'], ['Female', 'Male']);

        $this->assertSame(['Female', 'Male', 'NoAnswer'], $legend);
        $this->assertCount(3, $items);
        $this->assertSame('F', $items[0]['key']);
        $this->assertSame('Female', $items[0]['title']);
        $this->assertSame('NoAnswer', $items[2]['key']);

        $fValueAlias = $batch->countValue('Q1', 'F');
        $mValueAlias = $batch->countValue('Q1', 'M');
        $blankAlias = $batch->countBlank('Q1');
        $this->injectBatchResults($batch, [$fValueAlias => 3, $mValueAlias => 4, $blankAlias => 1]);

        $this->assertSame([3, 4, 1], $this->resolveAll(array_column($items, 'value')));
    }

    /**
     * @testdox buildItemsFromCodes() omits the NoAnswer bucket for question types not in the no-answer list
     */
    public function testBuildItemsFromCodesOmitsNoAnswerForOtherTypes()
    {
        $processor = new ConcreteTestQuestionProcessor();
        $processor->setQuestion([
            'sid' => 1,
            'gid' => 1,
            'qid' => 1,
            'title' => 'Q1',
            'type' => Question::QT_N_NUMERICAL,
        ]);
        $processor->setBatch(new ResponseAggregateBatch(1));

        [$legend, $items] = $processor->publicBuildItemsFromCodes('Q1', ['a'], ['A']);

        $this->assertSame(['A'], $legend);
        $this->assertCount(1, $items);
    }

    /**
     * @testdox buildItemsFromCodes() returns empty legend/items for an empty code list
     */
    public function testBuildItemsFromCodesWithNoCodes()
    {
        $processor = new ConcreteTestQuestionProcessor();
        $processor->setQuestion(['sid' => 1, 'gid' => 1, 'qid' => 1, 'type' => Question::QT_G_GENDER, 'title' => 'Q1']);
        $processor->setBatch(new ResponseAggregateBatch(1));

        [$legend, $items] = $processor->publicBuildItemsFromCodes('Q1', []);

        $this->assertSame([], $legend);
        $this->assertSame([], $items);
    }

    /**
     * @testdox batchGetResponseCounts() returns one deferred count per field, keyed by field name
     */
    public function testBatchGetResponseCounts()
    {
        $processor = new ConcreteTestQuestionProcessor();
        $processor->setQuestion(['sid' => 1, 'gid' => 1, 'qid' => 1, 'type' => Question::QT_L_LIST, 'title' => 'Q1']);
        $batch = new ResponseAggregateBatch(1);
        $processor->setBatch($batch);

        $counts = $processor->publicBatchGetResponseCounts(['Q1_S1', 'Q1_S2']);

        $this->assertSame(['Q1_S1', 'Q1_S2'], array_keys($counts));

        $alias1 = $batch->countNonEmpty('Q1_S1');
        $alias2 = $batch->countNonEmpty('Q1_S2');
        $this->injectBatchResults($batch, [$alias1 => 2, $alias2 => 5]);

        $this->assertSame(2, $counts['Q1_S1']());
        $this->assertSame(5, $counts['Q1_S2']());
    }

    /**
     * Calls a protected/private method via reflection, for coverage of
     * helpers with no public wrapper on the concrete test subclass.
     */
    private function callProtected(object $object, string $method, array $args)
    {
        $ref = new \ReflectionMethod($object, $method);
        return $ref->invokeArgs($object, $args);
    }
}
