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

        $sOrgdata = $this->getOrgData($groups, self::$testSurvey->questions);

        $groupHelper = new \LimeSurvey\Models\Services\GroupHelper();
        $groupHelper->reorderGroup(self::$surveyId, $sOrgdata);

        // Check the new order.
        $changedOrder = \Yii::app()->db->createCommand()->select('group_order')
                            ->from('{{groups}}')
                            ->query()
                            ->readAll();

        $this->assertSame('2', $changedOrder[0]['group_order'], 'The group id is incorrect.');
        $this->assertSame('1', $changedOrder[1]['group_order'], 'The group id is incorrect.');
        $this->assertSame('3', $changedOrder[2]['group_order'], 'The group id is incorrect.');

        // Test message.
        $flashMessage = $_SESSION['aFlashMessage'][0];

        $this->assertSame('success', $flashMessage['type'], 'Apparently, the new order was not saved correctly (See the flash message)');
        $this->assertSame('The new question group/question order was successfully saved.', $flashMessage['message'], 'Apparently, the new order was not saved correctly (See the flash message)');
    }

    private function getOrgData($groups, $questions)
    {
        $sOrgdata = '';

        foreach ($groups as $group) {
            $sOrgdata .= 'list[g' . $group->gid . ']=root&';
        }

        $lastkey = array_key_last($questions);

        foreach ($questions as $key => $question) {
            $sOrgdata .= 'list[q' . $question->qid . ']=' . 'g' . $question->gid;

            if ($key !== $lastkey) {
                $sOrgdata .= '&';
            }
        }

        return $sOrgdata;
    }
}
