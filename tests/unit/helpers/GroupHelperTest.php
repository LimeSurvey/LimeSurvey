<?php

namespace ls\tests;

class GroupHelperTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Yii::app()->setController(new DummyController('dummyid'));

        // Import survey
        $filename = self::$surveysFolder . '/survey_groupHelper_test.lss';
        self::importSurvey($filename);
    }

    /**
     * Testing that gid and group_order fields change
     * after changing group order with reorderGroup function.
     */
    public function testGroupOrderChange()
    {
        // Check the original order (Use sql to avoid cache).
        $currentOrder = \Yii::app()->db->createCommand()->select(['gid', 'group_order'])
                            ->from('{{groups}}')
                            ->where('sid=' . self::$surveyId)
                            ->order('group_order ASC')
                            ->query()
                            ->readAll();

        // Checking gid
        $this->assertEquals(self::$testSurvey->groups[0]->gid, $currentOrder[0]['gid'], 'Group found with an unexpected id');
        $this->assertEquals(self::$testSurvey->groups[1]->gid, $currentOrder[1]['gid'], 'Group found with an unexpected id');
        $this->assertEquals(self::$testSurvey->groups[2]->gid, $currentOrder[2]['gid'], 'Group found with an unexpected id');

        // Checking group order
        $this->assertEquals(self::$testSurvey->groups[0]->group_order, $currentOrder[0]['group_order'], 'Group found with an unexpected order position');
        $this->assertEquals(self::$testSurvey->groups[1]->group_order, $currentOrder[1]['group_order'], 'Group found with an unexpected order position');
        $this->assertEquals(self::$testSurvey->groups[2]->group_order, $currentOrder[2]['group_order'], 'Group found with an unexpected order position');

        // Change group order.
        $groups = array();
        $groups[0] = self::$testSurvey->groups[1];
        $groups[1] = self::$testSurvey->groups[2];
        $groups[2] = self::$testSurvey->groups[0];

        $orgdata = $this->getOrgData($groups, self::$testSurvey->questions);

        $groupHelper = new \LimeSurvey\Models\Services\GroupHelper();
        $result = $groupHelper->reorderGroup(self::$surveyId, $orgdata);

        // Check the new order (Use sql to avoid cache).
        $changedOrder = \Yii::app()->db->createCommand()->select(['gid', 'group_order'])
                            ->from('{{groups}}')
                            ->where('sid=' . self::$surveyId)
                            ->order('group_order ASC')
                            ->query()
                            ->readAll();

        // Checking gid
        $this->assertNotEquals(self::$testSurvey->groups[0]->gid, $changedOrder[0]['gid'], 'Group found with an unexpected id');
        $this->assertNotEquals(self::$testSurvey->groups[1]->gid, $changedOrder[1]['gid'], 'Group found with an unexpected id');
        $this->assertNotEquals(self::$testSurvey->groups[2]->gid, $changedOrder[2]['gid'], 'Group found with an unexpected id');

        // Checking group order
        $this->assertEquals(self::$testSurvey->groups[0]->group_order, $changedOrder[0]['group_order'], 'Group found with an unexpected order position');
        $this->assertEquals(self::$testSurvey->groups[1]->group_order, $changedOrder[1]['group_order'], 'Group found with an unexpected order position');
        $this->assertEquals(self::$testSurvey->groups[2]->group_order, $changedOrder[2]['group_order'], 'Group found with an unexpected order position');

        // Test result.
        $this->assertArrayHasKey('type', $result, 'Result of reorder operation is not as expected.');
        $this->assertSame('success', $result['type'], 'Result of reorder operation is not as expected.');
    }

    /**
     * Testing that qid and question_order fields change
     * after changing question order with reorderGroup function.
     *
     * Use questions in the first gruop.
     */
    public function testQuestionOrderChange()
    {
        $gid = self::$testSurvey->groups[0]->gid;
        $attributes = array('sid' => self::$surveyId, 'gid' => $gid);
        $criteria = new \CDbCriteria(array('order' => 'question_order ASC'));
        $firstGroupQuestions = \Question::model()->findAllByAttributes($attributes, $criteria);

        // Check the original order (Use sql to avoid cache).
        $currentOrder = \Yii::app()->db->createCommand()->select(['qid', 'question_order'])
                            ->from('{{questions}}')
                            ->where('gid=' . $gid)
                            ->order('question_order ASC')
                            ->query()
                            ->readAll();

        // Checking qid.
        $this->assertEquals($firstGroupQuestions[0]->qid, $currentOrder[0]['qid'], 'Question found with an unexpected id.');
        $this->assertEquals($firstGroupQuestions[1]->qid, $currentOrder[1]['qid'], 'Question found with an unexpected id.');

        // Checking question order.
        $this->assertEquals($firstGroupQuestions[0]->question_order, $currentOrder[0]['question_order'], 'Question found with an unexpected order position');
        $this->assertEquals($firstGroupQuestions[1]->question_order, $currentOrder[1]['question_order'], 'Question found with an unexpected order position');

        // Change question order.
        $questions = array();
        $questions[] = self::$testSurvey->questions[1];
        $questions[] = self::$testSurvey->questions[0];
        $questions[] = self::$testSurvey->questions[2];
        $questions[] = self::$testSurvey->questions[3];
        $questions[] = self::$testSurvey->questions[4];
        $questions[] = self::$testSurvey->questions[5];

        $orgdata = $this->getOrgData(self::$testSurvey->groups, $questions);

        $groupHelper = new \LimeSurvey\Models\Services\GroupHelper();
        $result = $groupHelper->reorderGroup(self::$surveyId, $orgdata);

        // Check the new order (Use sql to avoid cache).
        $newOrder = \Yii::app()->db->createCommand()->select(['qid', 'question_order'])
                            ->from('{{questions}}')
                            ->where('gid=' . $gid)
                            ->order('question_order ASC')
                            ->query()
                            ->readAll();

        // Checking qid.
        $this->assertNotEquals($firstGroupQuestions[0]->qid, $newOrder[0]['qid'], 'Question found with an unexpected id.');
        $this->assertNotEquals($firstGroupQuestions[1]->qid, $newOrder[1]['qid'], 'Question found with an unexpected id.');

        // Checking new question order (It should be the same since ordered by question_order field).
        $this->assertEquals($firstGroupQuestions[0]->question_order, $newOrder[0]['question_order'], 'Question found with an unexpected order position');
        $this->assertEquals($firstGroupQuestions[1]->question_order, $newOrder[1]['question_order'], 'Question found with an unexpected order position');

        // Test result.
        $this->assertArrayHasKey('type', $result, 'Result of reorder operation is not as expected.');
        $this->assertSame('success', $result['type'], 'Result of reorder operation is not as expected.');
    }

    /**
     * Test question group change.
     * Change Q03 from group two to group three.
     */
    public function testQuestionGroupChange()
    {
        $gid = self::$testSurvey->groups[2]->gid;

        // Check the original order (Use sql to avoid cache).
        $currentOrder = \Yii::app()->db->createCommand()->select('title')
                            ->from('{{questions}}')
                            ->where('gid=' . $gid)
                            ->order('question_order, title ASC')
                            ->query()
                            ->readAll();

        // Checking initial order (Q05 Q06) and number of questions in the group.
        $this->assertCount(2, $currentOrder, 'The number of questions in the group is not as expected.');
        $this->assertSame('Q05', $currentOrder[0]['title'], 'The question title is not as expected.');
        $this->assertSame('Q06', $currentOrder[1]['title'], 'The question title is not as expected.');

        // Change question group.
        $orgdata = $this->getOrgData(self::$testSurvey->groups, self::$testSurvey->questions);

        // Changing Q03 to another group.
        $qid = \Yii::app()->db->createCommand()->select('qid')
                            ->from('{{questions}}')
                            ->where('title = "Q03"')
                            ->andWhere('sid=' . self::$surveyId)
                            ->query()
                            ->readAll()[0]['qid'];

        $orgdata['q' . $qid] = 'g' . self::$testSurvey->groups[2]->gid;

        $groupHelper = new \LimeSurvey\Models\Services\GroupHelper();
        $result = $groupHelper->reorderGroup(self::$surveyId, $orgdata);

        // Check the new order (Use sql to avoid cache).
        $currentOrder = \Yii::app()->db->createCommand()->select('title')
                            ->from('{{questions}}')
                            ->where('gid=' . $gid)
                            ->order('question_order, title ASC')
                            ->query()
                            ->readAll();

        // Checking new order (Q03 Q05 Q06) and number of questions in the group.
        $this->assertCount(3, $currentOrder, 'The number of questions in the group is not as expected.');
        $this->assertSame('Q03', $currentOrder[0]['title'], 'The question title is not as expected.');
        $this->assertSame('Q05', $currentOrder[1]['title'], 'The question title is not as expected.');
        $this->assertSame('Q06', $currentOrder[2]['title'], 'The question title is not as expected.');

        // Test result.
        $this->assertArrayHasKey('type', $result, 'Result of reorder operation is not as expected.');
        $this->assertSame('success', $result['type'], 'Result of reorder operation is not as expected.');
    }

    /**
     * Testing that a question can not be changed
     * from one group to another on an active survey.
     */
    public function testQuestionGroupChangeOnActiveSurvey()
    {
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();

        $orgdata = $this->getOrgData(self::$testSurvey->groups, self::$testSurvey->questions);

        $groupHelper = new \LimeSurvey\Models\Services\GroupHelper();
        $result = $groupHelper->reorderGroup(self::$surveyId, $orgdata);

        // Asserting Q03 could not be moved back to group two.
        $this->assertArrayHasKey('type', $result, 'There is no type key in the response');
        $this->assertSame('error', $result['type'], 'Apparently, the question was moved from one group to another, this should not happen in active surveys.');
        $this->assertSame('Q03', $result['question-titles'][0], 'The question title is not the one expected.');
    }

    private function getOrgData($groups, $questions)
    {
        $orgdata = array();

        foreach ($groups as $group) {
            $orgdata['g' . $group->gid] = 'root';
        }

        foreach ($questions as $key => $question) {
            $orgdata['q' . $question->qid] = 'g' . $question->gid;
        }

        return $orgdata;
    }
}
