<?php

namespace ls\tests;

class GroupHelper extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        \Yii::import('application.models.services.GroupHelper', true);

        parent::setUpBeforeClass();

        \Yii::app()->setController(new DummyController('dummyid'));

        // Import survey
        $filename = self::$surveysFolder . '/survey_groupHelper_test.lss';
        self::importSurvey($filename);
    }

    /**
     * Test group order change.
     */
    public function testGroupOrderChange()
    {
        // Check the original order.
        $currentOrder = \Yii::app()->db->createCommand()->select('group_order')
                            ->from('{{groups}}')
                            ->query()
                            ->readAll();

        $this->assertSame('1', $currentOrder[0]['group_order'], 'The group id is incorrect.');
        $this->assertSame('2', $currentOrder[1]['group_order'], 'The group id is incorrect.');
        $this->assertSame('3', $currentOrder[2]['group_order'], 'The group id is incorrect.');

        // Change group order.
        $groups = array();
        $groups[0] = self::$testSurvey->groups[1];
        $groups[1] = self::$testSurvey->groups[0];
        $groups[2] = self::$testSurvey->groups[2];

        $orgdata = $this->getOrgData($groups, self::$testSurvey->questions);

        $groupHelper = new \LimeSurvey\Models\Services\GroupHelper();
        $result = $groupHelper->reorderGroup(self::$surveyId, $orgdata);

        // Check the new order.
        $changedOrder = \Yii::app()->db->createCommand()->select('group_order')
                            ->from('{{groups}}')
                            ->query()
                            ->readAll();

        $this->assertSame('2', $changedOrder[0]['group_order'], 'The group id is incorrect.');
        $this->assertSame('1', $changedOrder[1]['group_order'], 'The group id is incorrect.');
        $this->assertSame('3', $changedOrder[2]['group_order'], 'The group id is incorrect.');

        // Test result.
        $this->assertArrayHasKey('type', $result, 'The returned value is not correct.');
        $this->assertSame('success', $result['type'], 'The returned value is not correct.');
    }

    /**
     * Test question order change.
     * Use questions in the first group.
     */
    public function testQuestionOrderChange()
    {
        $gid = self::$testSurvey->groups[0]->gid;

        // Check the original order.
        $currentOrder = \Yii::app()->db->createCommand()->select('question_order')
                            ->from('{{questions}}')
                            ->where('gid=' . $gid)
                            ->query()
                            ->readAll();

        $this->assertSame('0', $currentOrder[0]['question_order'], 'The question id is incorrect.');
        $this->assertSame('1', $currentOrder[1]['question_order'], 'The question id is incorrect.');

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

        // Check the new order.
        $currentOrder = \Yii::app()->db->createCommand()->select('question_order')
                            ->from('{{questions}}')
                            ->where('gid=' . $gid)
                            ->query()
                            ->readAll();

        $this->assertSame('1', $currentOrder[0]['question_order'], 'The question id is incorrect.');
        $this->assertSame('0', $currentOrder[1]['question_order'], 'The question id is incorrect.');

        // Test result.
        $this->assertArrayHasKey('type', $result, 'The returned value is not correct.');
        $this->assertSame('success', $result['type'], 'The returned value is not correct.');
    }

    /**
     * Test question group change.
     * Change Q03 from group two to group three.
     */
    public function testQuestionGroupChange()
    {
        $gid = self::$testSurvey->groups[2]->gid;

        // Check the original order.
        $currentOrder = \Yii::app()->db->createCommand()->select('title')
                            ->from('{{questions}}')
                            ->where('gid=' . $gid)
                            ->query()
                            ->readAll();

        $this->assertCount(2, $currentOrder, 'The number of questions in the group is not correct.');
        $this->assertSame('Q05', $currentOrder[0]['title'], 'The question title is incorrect.');
        $this->assertSame('Q06', $currentOrder[1]['title'], 'The question title is incorrect.');

        // Change question group.
        $orgdata = $this->getOrgData(self::$testSurvey->groups, self::$testSurvey->questions);

        $qid = \Yii::app()->db->createCommand()->select('qid')
                            ->from('{{questions}}')
                            ->where('title = "Q03"')
                            ->query()
                            ->readAll()[0]['qid'];

        $orgdata['q' . $qid] = 'g' . self::$testSurvey->groups[2]->gid;

        $groupHelper = new \LimeSurvey\Models\Services\GroupHelper();
        $result = $groupHelper->reorderGroup(self::$surveyId, $orgdata);

        // Check the original order.
        $currentOrder = \Yii::app()->db->createCommand()->select('title')
                            ->from('{{questions}}')
                            ->where('gid=' . $gid)
                            ->query()
                            ->readAll();

        $this->assertCount(3, $currentOrder, 'The number of questions in the group is not correct.');
        $this->assertSame('Q03', $currentOrder[0]['title'], 'The question title is incorrect.');
        $this->assertSame('Q05', $currentOrder[1]['title'], 'The question title is incorrect.');
        $this->assertSame('Q06', $currentOrder[2]['title'], 'The question title is incorrect.');

        // Test result.
        $this->assertArrayHasKey('type', $result, 'The returned value is not correct.');
        $this->assertSame('success', $result['type'], 'The returned value is not correct.');
    }

    /**
     * Test group order change on an active survey.
     */
    public function testQuestionGroupChangeOnActiveSurvey()
    {
        $activator = new \SurveyActivator(self::$testSurvey);
        $result = $activator->activate();

        $orgdata = $this->getOrgData(self::$testSurvey->groups, self::$testSurvey->questions);

        $groupHelper = new \LimeSurvey\Models\Services\GroupHelper();
        $result = $groupHelper->reorderGroup(self::$surveyId, $orgdata);

        // Asserting Q03 could not be moved back to group two.
        $this->assertArrayHasKey('type', $result, 'The returned value is not correct.');
        $this->assertSame('error', $result['type'], 'The returned value is not correct.');
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
