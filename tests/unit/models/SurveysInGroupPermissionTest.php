<?php

namespace ls\tests;

/**
 * Permission tests for survey owners and the SurveysInGroup permission,
 * including the ownerManageAllSurveysInGroup setting.
 * Uses records loaded from the database, so owner ids have the type the
 * DB driver returns (e.g. string on MySQL, see #17052).
 */
class SurveysInGroupPermissionTest extends TestBaseClass
{
    /** @var \User Owner of the test survey */
    private $surveyOwner;

    /** @var \User Owner of the test survey group */
    private $groupOwner;

    /** @var \User User without any relation to the survey or group */
    private $otherUser;

    /** @var \SurveysGroups */
    private $surveysGroup;

    /** @var \Survey */
    private $survey;

    /** @var bool Original value of ownerManageAllSurveysInGroup */
    private $ownerManageAllSurveysInGroup;

    /**
     * Creates three users without permissions, a survey group owned by
     * one of them and a survey in that group owned by another one.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->ownerManageAllSurveysInGroup = \Yii::app()->getConfig('ownerManageAllSurveysInGroup');
        \Yii::app()->session['loginID'] = 1;

        $this->surveyOwner = $this->createTestUser('survey_owner');
        $this->groupOwner = $this->createTestUser('group_owner');
        $this->otherUser = $this->createTestUser('other_user');

        $this->surveysGroup = new \SurveysGroups();
        $this->surveysGroup->name = 'perm_' . \Yii::app()->securityManager->generateRandomString(8);
        $this->surveysGroup->title = 'Permission test group';
        $this->surveysGroup->sortorder = 1;
        $this->surveysGroup->created_by = 1;
        $this->surveysGroup->owner_id = $this->groupOwner->uid;
        $this->assertTrue(
            $this->surveysGroup->save(false),
            'Could not save survey group: ' . json_encode($this->surveysGroup->getErrors())
        );

        $survey = \Survey::model()->insertNewSurvey([
            'gsid' => $this->surveysGroup->gsid,
            'owner_id' => $this->surveyOwner->uid,
        ]);
        $this->assertNotEmpty($survey->sid, 'Could not save survey: ' . json_encode($survey->getErrors()));
        // Reload from DB so owner_id/gsid have the type the DB driver returns
        \Survey::model()->resetCache();
        $this->survey = \Survey::model()->findByPk($survey->sid);
    }

    /**
     * Deletes the test survey, group, users and their permissions and
     * restores the ownerManageAllSurveysInGroup setting.
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->setConfig('ownerManageAllSurveysInGroup', $this->ownerManageAllSurveysInGroup);
        \Yii::app()->session['loginID'] = 1;
        if ($this->survey) {
            $this->survey->delete();
        }
        if ($this->surveysGroup) {
            \SurveysGroups::model()->deleteByPk($this->surveysGroup->gsid);
        }
        foreach ([$this->surveyOwner, $this->groupOwner, $this->otherUser] as $user) {
            if ($user) {
                \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
                $user->delete();
            }
        }
        \Survey::model()->resetCache();
        parent::tearDown();
    }

    /**
     * The survey owner has all survey permissions without any row in
     * the permissions table (#17052).
     * @return void
     */
    public function testSurveyOwnerHasPermissionWithoutPermissionRow()
    {
        $sid = $this->survey->sid;
        $ownerId = (int) $this->surveyOwner->uid;
        $this->assertNull(
            \Permission::model()->findByAttributes(['uid' => $ownerId, 'entity' => 'survey', 'entity_id' => $sid]),
            'Survey owner must not have a survey permission row for this test'
        );

        $this->assertTrue($this->survey->hasPermission('survey', 'read', $ownerId));
        $this->assertTrue($this->survey->hasPermission('survey', 'update', $ownerId));
        $this->assertTrue($this->survey->hasPermission('survey', 'delete', $ownerId));
        $this->assertTrue($this->survey->hasPermission('surveycontent', 'update', $ownerId));
        $this->assertTrue($this->survey->hasPermission('responses', 'read', $ownerId));
        $this->assertTrue(\Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update', $ownerId));

        $otherId = (int) $this->otherUser->uid;
        $this->assertFalse($this->survey->hasPermission('survey', 'read', $otherId));
        $this->assertFalse($this->survey->hasPermission('surveycontent', 'update', $otherId));
        $this->assertFalse(\Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update', $otherId));
    }

    /**
     * The owner check also matches when the DB driver returns owner_id as
     * string (e.g. sqlsrv), while the user id is an integer (#17052).
     * @return void
     */
    public function testSurveyOwnerHasPermissionWithStringOwnerId()
    {
        // Same instance as returned by Survey::model()->findByPk() in Permission::getEntity()
        $this->survey->owner_id = (string) $this->surveyOwner->uid;
        $ownerId = (int) $this->surveyOwner->uid;

        $this->assertTrue($this->survey->hasPermission('survey', 'read', $ownerId));
        $this->assertTrue($this->survey->hasPermission('surveycontent', 'update', $ownerId));
        $this->assertFalse($this->survey->hasPermission('survey', 'read', (int) $this->otherUser->uid));
    }

    /**
     * The survey owner sees the survey in the survey list, other users don't.
     * @return void
     */
    public function testSurveyOwnerSeesSurveyInList()
    {
        $this->assertContains((int) $this->survey->sid, $this->getListedSurveyIds($this->surveyOwner->uid));
        $this->assertNotContains((int) $this->survey->sid, $this->getListedSurveyIds($this->otherUser->uid));
    }

    /**
     * With ownerManageAllSurveysInGroup enabled the group owner has all
     * permissions on surveys in the group and sees them in the list.
     * @return void
     */
    public function testGroupOwnerManagesSurveysInGroupWhenEnabled()
    {
        \Yii::app()->setConfig('ownerManageAllSurveysInGroup', true);
        $groupOwnerId = (int) $this->groupOwner->uid;

        $this->assertTrue($this->survey->hasPermission('survey', 'read', $groupOwnerId));
        $this->assertTrue($this->survey->hasPermission('survey', 'update', $groupOwnerId));
        $this->assertTrue($this->survey->hasPermission('surveycontent', 'update', $groupOwnerId));
        $this->assertTrue($this->survey->hasPermission('responses', 'read', $groupOwnerId));
        $this->assertContains((int) $this->survey->sid, $this->getListedSurveyIds($groupOwnerId));
    }

    /**
     * With ownerManageAllSurveysInGroup disabled the group owner gets no
     * permission on surveys in the group, the survey owner still does.
     * @return void
     */
    public function testGroupOwnerHasNoPermissionWhenDisabled()
    {
        \Yii::app()->setConfig('ownerManageAllSurveysInGroup', false);
        $groupOwnerId = (int) $this->groupOwner->uid;

        $this->assertFalse($this->survey->hasPermission('survey', 'read', $groupOwnerId));
        $this->assertFalse($this->survey->hasPermission('surveycontent', 'update', $groupOwnerId));
        $this->assertNotContains((int) $this->survey->sid, $this->getListedSurveyIds($groupOwnerId));

        $surveyOwnerId = (int) $this->surveyOwner->uid;
        $this->assertTrue($this->survey->hasPermission('survey', 'update', $surveyOwnerId));
        $this->assertContains((int) $this->survey->sid, $this->getListedSurveyIds($surveyOwnerId));
    }

    /**
     * An explicit "Surveys in this group" permission gives access to the
     * surveys of the group, independent of ownerManageAllSurveysInGroup.
     * @return void
     */
    public function testSurveysInGroupPermissionGrantsAccess()
    {
        \Yii::app()->setConfig('ownerManageAllSurveysInGroup', false);
        $otherId = (int) $this->otherUser->uid;
        $this->setSurveysInGroupPermission($otherId, ['read_p' => 1]);

        $this->assertTrue($this->survey->hasPermission('survey', 'read', $otherId));
        $this->assertTrue($this->survey->hasPermission('responses', 'read', $otherId));
        $this->assertFalse($this->survey->hasPermission('survey', 'update', $otherId));
        $this->assertFalse($this->survey->hasPermission('surveycontent', 'update', $otherId));
        $this->assertContains((int) $this->survey->sid, $this->getListedSurveyIds($otherId));
    }

    /**
     * A "Surveys in this group" permission without read gives the granted
     * right, but the survey is not shown in the survey list.
     * @return void
     */
    public function testSurveysInGroupPermissionWithoutReadIsNotListed()
    {
        $otherId = (int) $this->otherUser->uid;
        $this->setSurveysInGroupPermission($otherId, ['update_p' => 1]);

        $this->assertTrue($this->survey->hasPermission('surveycontent', 'update', $otherId));
        $this->assertFalse($this->survey->hasPermission('survey', 'read', $otherId));
        $this->assertNotContains((int) $this->survey->sid, $this->getListedSurveyIds($otherId));
    }

    /**
     * Creates a user without any permission.
     * @param string $prefix Prefix for the random user name
     * @return \User
     */
    private function createTestUser($prefix)
    {
        $userName = $prefix . '_' . \Yii::app()->securityManager->generateRandomString(8);
        return self::createUserWithPermissions([
            'users_name' => $userName,
            'full_name' => $userName,
            'email' => $userName . '@example.org',
            'lang' => 'auto',
            'password' => 'testpassword123',
        ]);
    }

    /**
     * Adds a "Surveys in this group" permission on the test group.
     * @param int $userId
     * @param array $crud CRUD columns to set, e.g. ['read_p' => 1]
     * @return void
     */
    private function setSurveysInGroupPermission($userId, array $crud)
    {
        $permission = new \Permission();
        $permission->entity = 'surveysingroup';
        $permission->entity_id = $this->surveysGroup->gsid;
        $permission->uid = $userId;
        $permission->permission = 'surveys';
        foreach ($crud as $column => $value) {
            $permission->$column = $value;
        }
        $this->assertTrue($permission->save(), 'Could not save permission: ' . json_encode($permission->getErrors()));
    }

    /**
     * Returns the ids of the surveys a user sees in the survey list.
     * @param int $userId
     * @return int[]
     */
    private function getListedSurveyIds($userId)
    {
        $criteria = new \CDbCriteria();
        $criteria->compare('t.gsid', $this->surveysGroup->gsid);
        $surveys = \Survey::model()->permission($userId)->findAll($criteria);
        return array_map(function ($survey) {
            return (int) $survey->sid;
        }, $surveys);
    }
}
