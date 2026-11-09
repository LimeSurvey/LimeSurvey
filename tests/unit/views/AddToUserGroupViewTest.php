<?php

namespace ls\tests;

/**
 * The user group select of the "Add to user group" mass action in user management
 * must encode the group names.
 */
class AddToUserGroupViewTest extends TestBaseClass
{
    /**
     * A group name with HTML-special characters is output encoded, not as markup.
     * @return void
     */
    public function testGroupNameIsEncoded()
    {
        $originalLoginId = \Yii::app()->session['loginID'] ?? null;
        // setId() writes both the user state and session['loginID'], so both are saved and restored
        $originalUserState = \Yii::app()->user->getState('__id');
        $groupName = "Grp '20831' & <i>";
        // A leftover group of an aborted run would fail the unique rule
        \UserGroup::model()->deleteAllByAttributes(['name' => $groupName]);
        $userGroup = new \UserGroup();
        $userGroup->name = $groupName;
        $userGroup->description = '';
        $userGroup->owner_id = 1;
        $this->assertTrue($userGroup->save(), 'The test user group could not be saved.');
        try {
            \Yii::app()->session['loginID'] = 1;
            \Yii::app()->user->setId(1);

            $controller = new \CController('test');
            $html = $controller->renderFile(
                APPPATH . 'views/userManagement/massiveAction/_addtousergroup.php',
                [],
                true
            );

            $this->assertStringContainsString(
                '<option value="' . $userGroup->ugid . '">' . \CHtml::encode($groupName) . '</option>',
                $html
            );
            $this->assertStringNotContainsString($groupName, $html);
        } finally {
            \Yii::app()->user->setState('__id', $originalUserState);
            \Yii::app()->session['loginID'] = $originalLoginId;
            $userGroup->delete();
        }
    }
}
