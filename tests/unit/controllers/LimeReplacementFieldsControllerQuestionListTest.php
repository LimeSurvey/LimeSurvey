<?php

namespace ls\tests\unit\controllers;

use ls\tests\TestBaseClass;

require_once __DIR__ . '/../../../application/controllers/LimereplacementfieldsController.php';

/**
 * Question list of the placeholder picker ("Placeholder fields") for subquestion/answer option texts
 */
class LimeReplacementFieldsControllerQuestionListTest extends TestBaseClass
{
    /**
     * Groups 2 and 3 share the same group_order (happens after imports), group 3 comes first in the fieldmap.
     * @var array<int, int> group_order indexed by gid
     */
    private $groupOrders = [1 => 0, 2 => 1, 3 => 1, 4 => 2];

    /** @var array */
    private $fieldmap = [
        ['qid' => 11, 'gid' => 1],
        ['qid' => 31, 'gid' => 3],
        ['qid' => 21, 'gid' => 2],
        ['qid' => 22, 'gid' => 2],
        ['qid' => 41, 'gid' => 4],
    ];

    /**
     * @param int $gid
     * @param int $qid
     * @return int[] the qids of the question list
     */
    private function getQuestionListQids($gid, $qid)
    {
        $controller = new \LimeReplacementFieldsController('limereplacementfields');
        $property = new \ReflectionProperty($controller, 'groupOrders');
        $property->setAccessible(true);
        $property->setValue($controller, $this->groupOrders);
        $method = new \ReflectionMethod($controller, 'getQuestionList');
        $method->setAccessible(true);
        $questionList = $method->invoke($controller, 'editanswer', $gid, $qid, $this->fieldmap, 'editanswer', 'G');
        return array_column($questionList, 'qid');
    }

    public function testUnsavedQuestionListsEarlierGroupsAndTheWholeCurrentGroup()
    {
        $this->assertSame([11, 21, 22], $this->getQuestionListQids(2, 0));
    }

    public function testUnsavedQuestionWithoutGroupListsAllQuestions()
    {
        $this->assertSame([11, 31, 21, 22, 41], $this->getQuestionListQids(0, 0));
    }

    public function testSavedQuestionListsQuestionsBeforeItInTheFieldmap()
    {
        $this->assertSame([11, 31, 21], $this->getQuestionListQids(2, 22));
    }
}
