<?php

namespace ls\tests\controllers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * Tests for the "Owner" field of survey groups: it is saved on creation, and
 * on update only the group owner or a superadmin can change it (#16919).
 */
class SurveysGroupsOwnerTest extends TestBaseClass
{
    /** @var \User Owner of the test survey group */
    private $groupOwner;

    /** @var \User User with update permission on the test group, but not its owner */
    private $groupEditor;

    /** @var \User User with the global permission to create survey groups */
    private $groupCreator;

    /** @var \SurveysGroups Survey group used by the update tests */
    private $surveysGroup;

    /** @var int[] Ids of survey groups created through the create action */
    private $createdGroupIds = [];

    /** @var DummyController Controller recording redirects, with rendering stubbed out */
    private $controller;

    /**
     * Creates the test users and a survey group owned by one of them.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.controllers.admin.SurveysGroupsController', true);
        \Yii::app()->user->setId(1);

        $this->groupOwner = $this->createTestUser('sg_owner');
        $this->groupEditor = $this->createTestUser('sg_editor');
        $this->groupCreator = $this->createTestUser('sg_creator', [
            'surveysgroups' => ['create' => 'on', 'read' => 'on'],
        ]);

        $this->surveysGroup = new \SurveysGroups();
        $this->surveysGroup->name = $this->getNewGroupCode();
        $this->surveysGroup->title = 'Owner test group';
        $this->surveysGroup->sortorder = 1;
        $this->surveysGroup->created_by = 1;
        $this->surveysGroup->owner_id = $this->groupOwner->uid;
        $this->assertTrue(
            $this->surveysGroup->save(),
            'Could not save survey group: ' . json_encode($this->surveysGroup->getErrors())
        );

        // The editor can read and update the group, but does not own it
        $permission = new \Permission();
        $permission->entity = 'surveysgroups';
        $permission->entity_id = $this->surveysGroup->gsid;
        $permission->uid = $this->groupEditor->uid;
        $permission->permission = 'group';
        $permission->read_p = 1;
        $permission->update_p = 1;
        $this->assertTrue($permission->save(), 'Could not save permission: ' . json_encode($permission->getErrors()));

        // Rendering needs a full HTTP request context, which unit tests don't have
        $this->controller = new class ('dummyid') extends DummyController {
            public function renderPartial($view, $data = null, $return = false, $processOutput = false)
            {
                return '';
            }
        };
        \Yii::app()->setController($this->controller);
    }

    /**
     * Deletes the test survey groups, users and their permissions.
     * @return void
     */
    protected function tearDown(): void
    {
        $_POST = [];
        \Yii::app()->user->setId(1);
        $groupIds = array_merge([$this->surveysGroup->gsid], $this->createdGroupIds);
        foreach ($groupIds as $gsid) {
            \SurveysGroupsettings::model()->deleteByPk($gsid);
            \SurveysGroups::model()->deleteByPk($gsid);
            \Permission::model()->deleteAllByAttributes(['entity' => 'surveysgroups', 'entity_id' => $gsid]);
        }
        foreach ([$this->groupOwner, $this->groupEditor, $this->groupCreator] as $user) {
            \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
            $user->delete();
        }
        parent::tearDown();
    }

    /**
     * The owner selected in the "Create survey group" form is saved, together
     * with the creator and the survey group settings.
     * @return void
     */
    public function testCreateSavesSelectedOwner()
    {
        \Yii::app()->user->setId($this->groupCreator->uid);
        $group = $this->createGroup(['owner_id' => $this->groupOwner->uid]);

        $this->assertEquals($this->groupOwner->uid, $group->owner_id);
        $this->assertEquals($this->groupCreator->uid, $group->created_by);
        $this->assertNotNull(
            \SurveysGroupsettings::model()->findByPk($group->gsid),
            'Survey group settings were not created'
        );
        $this->assertArrayHasKey('redirect', $this->controller->lastAction, 'No redirect after successful creation');
    }

    /**
     * Without a selected owner, the user creating the group becomes its owner.
     * @return void
     */
    public function testCreateWithoutOwnerUsesCurrentUser()
    {
        \Yii::app()->user->setId($this->groupCreator->uid);
        $group = $this->createGroup([]);

        $this->assertEquals($this->groupCreator->uid, $group->owner_id);
    }

    /**
     * A user who can update the group, but is neither its owner nor a
     * superadmin, can't change the owner. The other fields are still saved.
     * @return void
     */
    public function testUpdateByNonOwnerKeepsOwner()
    {
        \Yii::app()->user->setId($this->groupEditor->uid);
        $this->updateGroup([
            'title' => 'Updated by editor',
            'owner_id' => $this->groupEditor->uid,
        ]);

        $group = \SurveysGroups::model()->findByPk($this->surveysGroup->gsid);
        $this->assertEquals('Updated by editor', $group->title);
        $this->assertEquals($this->groupOwner->uid, $group->owner_id);
    }

    /**
     * The owner of the group can hand it over to another user.
     * @return void
     */
    public function testUpdateByOwnerChangesOwner()
    {
        \Yii::app()->user->setId($this->groupOwner->uid);
        $this->updateGroup(['owner_id' => $this->groupEditor->uid]);

        $group = \SurveysGroups::model()->findByPk($this->surveysGroup->gsid);
        $this->assertEquals($this->groupEditor->uid, $group->owner_id);
    }

    /**
     * A superadmin can change the owner of any group.
     * @return void
     */
    public function testUpdateBySuperadminChangesOwner()
    {
        \Yii::app()->user->setId(1);
        $this->updateGroup(['owner_id' => $this->groupEditor->uid]);

        $group = \SurveysGroups::model()->findByPk($this->surveysGroup->gsid);
        $this->assertEquals($this->groupEditor->uid, $group->owner_id);
    }

    /**
     * Submits the "Create survey group" form.
     * @param array $attributes Additional SurveysGroups attributes to post
     * @return \SurveysGroups The created group, loaded from the database
     */
    private function createGroup(array $attributes)
    {
        $name = $this->getNewGroupCode();
        $_POST['SurveysGroups'] = array_merge([
            'name' => $name,
            'title' => 'Created owner test group',
            'sortorder' => 1,
        ], $attributes);
        $this->getAction('create')->create();

        $group = \SurveysGroups::model()->findByAttributes(['name' => $name]);
        $this->assertNotNull($group, 'Survey group was not created');
        $this->createdGroupIds[] = $group->gsid;
        return $group;
    }

    /**
     * Submits the "Update survey group" form for the test group.
     * @param array $attributes SurveysGroups attributes to post
     * @return void
     */
    private function updateGroup(array $attributes)
    {
        $_POST['SurveysGroups'] = $attributes;
        $this->getAction('update')->update((int) $this->surveysGroup->gsid);
    }

    /**
     * Returns the survey groups admin action with the final rendering stubbed out.
     * @param string $id Action id
     * @return \SurveysGroupsController
     */
    private function getAction($id)
    {
        return new class ($this->controller, $id) extends \SurveysGroupsController {
            protected function renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
            {
            }
        };
    }

    /**
     * Returns an unused survey group code.
     * @return string
     */
    private function getNewGroupCode()
    {
        return 'sgown_' . uniqid();
    }

    /**
     * Creates a user with the given global permissions.
     * @param string $prefix Prefix for the random user name
     * @param array $permissions Global permissions, e.g. ['surveysgroups' => ['create' => 'on']]
     * @return \User
     */
    private function createTestUser($prefix, array $permissions = [])
    {
        $userName = $prefix . '_' . uniqid();
        return self::createUserWithPermissions([
            'users_name' => $userName,
            'full_name' => $userName,
            'email' => $userName . '@example.org',
            'lang' => 'auto',
            'password' => 'testpassword123',
        ], $permissions);
    }
}
