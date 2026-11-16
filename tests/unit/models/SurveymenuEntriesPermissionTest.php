<?php

namespace ls\tests;

/**
 * Permission checks of survey menu entries and the survey side menu
 * built from them (#17782).
 */
class SurveymenuEntriesPermissionTest extends TestBaseClass
{
    /** @var \User User with only survey read and participant read permissions */
    private $user;

    /** @var \Survey */
    private $survey;

    /**
     * Creates a survey owned by the admin and a user that may only read
     * the survey and its participants.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Yii::app()->session['loginID'] = 1;
        $this->survey = \Survey::model()->insertNewSurvey(['owner_id' => 1]);
        $this->assertNotEmpty($this->survey->sid, 'Could not save survey: ' . json_encode($this->survey->getErrors()));

        $userName = 'menuperm_' . \Yii::app()->securityManager->generateRandomString(8);
        $this->user = self::createUserWithPermissions([
            'users_name' => $userName,
            'full_name' => $userName,
            'email' => $userName . '@example.org',
            'lang' => 'auto',
            'password' => 'testpassword123',
        ]);
        $this->setSurveyPermission('survey', ['read_p' => 1]);
        $this->setSurveyPermission('tokens', ['read_p' => 1]);
        \Yii::app()->session['loginID'] = $this->user->uid;
    }

    /**
     * Deletes the test survey, the test user and their permissions.
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->session['loginID'] = 1;
        if ($this->survey) {
            $this->survey->delete();
        }
        if ($this->user) {
            \Permission::model()->deleteAllByAttributes(['uid' => $this->user->uid]);
            $this->user->delete();
        }
        parent::tearDown();
    }

    /**
     * Entries without a permission are always allowed.
     * @return void
     */
    public function testEntryWithoutPermissionIsAllowed()
    {
        $entry = new \SurveymenuEntries();
        $entry->permission = '';
        $entry->permission_grade = '';
        $this->assertTrue($entry->userHasPermission($this->survey->sid));
    }

    /**
     * Survey permissions are checked on the given survey.
     * @return void
     */
    public function testSurveyPermissionIsCheckedOnSurvey()
    {
        $this->assertTrue($this->getEntry('participants')->userHasPermission($this->survey->sid));
        $this->assertFalse($this->getEntry('quotas')->userHasPermission($this->survey->sid));
        $this->assertFalse($this->getEntry('responses')->userHasPermission($this->survey->sid));
    }

    /**
     * Global permissions are checked globally.
     * @return void
     */
    public function testGlobalPermissionIsCheckedGlobally()
    {
        $entry = new \SurveymenuEntries();
        $entry->permission = 'settings';
        $entry->permission_grade = 'read';
        $this->assertFalse($entry->userHasPermission($this->survey->sid));

        \Yii::app()->session['loginID'] = 1;
        $this->assertTrue($entry->userHasPermission($this->survey->sid));
    }

    /**
     * The survey side menu only contains entries the user has permission for.
     * @return void
     */
    public function testSideMenuOnlyContainsPermittedEntries()
    {
        \Yii::import('ext.admin.survey.SurveySidemenuWidget.SurveySidemenuWidget');
        $widget = new \SurveySidemenuWidget();
        $widget->sid = $this->survey->sid;
        $widget->surveyEntry = \SurveymenuEntries::model();
        $sideMenu = $widget->getSideMenu();

        $menuTitles = array_column($sideMenu['menu'], 'name');
        $this->assertSame([$this->getEntry('participants')->menu_title], $menuTitles);
        // The participant settings need the permission of the participant settings entry, not of the participants one
        $this->assertSame([], $sideMenu['settings']);
        $this->assertSame([], $sideMenu['presentation']);

        $widget->sideMenu = $sideMenu;
        $this->assertNull($widget->getPanelUrl('settings'));
        $this->assertSame($sideMenu['menu'][0]['url'], $widget->getPanelUrl('menu'));
    }

    /**
     * Gets a default survey menu entry by name.
     * @param string $name
     * @return \SurveymenuEntries
     */
    private function getEntry($name)
    {
        $entry = \SurveymenuEntries::model()->find('name=:name', [':name' => $name]);
        $this->assertNotNull($entry, "Survey menu entry $name not found");
        return $entry;
    }

    /**
     * Adds a survey permission on the test survey for the test user.
     * @param string $permissionName
     * @param array $crud CRUD columns to set, e.g. ['read_p' => 1]
     * @return void
     */
    private function setSurveyPermission($permissionName, array $crud)
    {
        $permission = new \Permission();
        $permission->entity = 'survey';
        $permission->entity_id = $this->survey->sid;
        $permission->uid = $this->user->uid;
        $permission->permission = $permissionName;
        foreach (['create_p', 'read_p', 'update_p', 'delete_p', 'import_p', 'export_p'] as $column) {
            $permission->$column = $crud[$column] ?? 0;
        }
        $this->assertTrue($permission->save(), 'Could not save permission: ' . json_encode($permission->getErrors()));
    }
}
