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
