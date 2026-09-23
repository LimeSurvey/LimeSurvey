<?php

namespace ls\tests;

/**
 * Regression tests for Mantis #19077: a non-superadmin's survey group list showed
 * the number of surveys instead of the number of survey groups, and the group
 * dropdown used to assign a survey to a group listed every group in the system.
 */
class SurveysGroupsSearchTest extends TestBaseClass
{
    /** @var int */
    private static $restrictedUserId;

    /** @var int */
    private static $otherUserId;

    /** @var int */
    private static $ownGroupGsid;

    /** @var int */
    private static $otherGroupGsid;

    /** @var int[] */
    private static $surveyIds = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::cleanupGroupFixtures();

        self::$restrictedUserId = self::createUserWithPermissions(
            [
                'users_name' => 'sgSearchRestricted',
                'full_name'  => 'SG Search Restricted',
                'email'      => 'sgsearchrestricted@example.com',
                'lang'       => 'auto',
                'password'   => 'sgSearchRestricted1',
            ],
            [
                'surveysgroups' => [
                    'create' => 'on',
                ],
            ]
        )->uid;

        self::$otherUserId = self::createUserWithPermissions(
            [
                'users_name' => 'sgSearchOtherOwner',
                'full_name'  => 'SG Search Other Owner',
                'email'      => 'sgsearchotherowner@example.com',
                'lang'       => 'auto',
                'password'   => 'sgSearchOtherOwner1',
            ],
            [
                'surveysgroups' => [
                    'create' => 'on',
                ],
            ]
        )->uid;

        // Group owned by the restricted user. SurveysGroups::getPermissionCriteria()
        // grants access to it via ownership (t.owner_id), but the same criteria also
        // LEFT JOINs surveys/permissions to check per-survey access, which fans this
        // single group row out into one row per survey once surveys are added below.
        self::$ownGroupGsid = self::createGroup('SGSearchOwnGroup', self::$restrictedUserId);

        // A group owned by someone else: the restricted user has no ownership, group
        // permission or survey permission granting access to it.
        self::$otherGroupGsid = self::createGroup('SGSearchOtherGroup', self::$otherUserId);

        // insertNewSurvey() validates gsid against the current user's permitted
        // groups (Survey::rules()), so create the surveys as the group's owner.
        $originalLoginId = \Yii::app()->session['loginID'] ?? null;
        \Yii::app()->session['loginID'] = self::$restrictedUserId;
        try {
            foreach (range(1, 3) as $i) {
                $survey = \Survey::model()->insertNewSurvey(['gsid' => self::$ownGroupGsid]);
                if (empty($survey->sid)) {
                    throw new \Exception('Could not create test survey: ' . print_r($survey->getErrors(), true));
                }
                self::$surveyIds[] = $survey->sid;
            }
        } finally {
            \Yii::app()->session['loginID'] = $originalLoginId;
        }
    }

    public static function tearDownAfterClass(): void
    {
        \Yii::app()->session['loginID'] = 1;

        foreach (self::$surveyIds as $sid) {
            \Survey::model()->deleteSurvey($sid);
        }
        self::$surveyIds = [];

        self::cleanupGroupFixtures();

        \User::model()->deleteAllByAttributes(['users_name' => 'sgSearchRestricted']);
        \User::model()->deleteAllByAttributes(['users_name' => 'sgSearchOtherOwner']);

        parent::tearDownAfterClass();
    }

    /**
     * Removes any leftover fixture groups (and their settings/surveys) from a
     * previous, possibly failed, run so this test class can run repeatably.
     * @return void
     */
    private static function cleanupGroupFixtures()
    {
        foreach (['SGSearchOwnGroup', 'SGSearchOtherGroup'] as $name) {
            $group = \SurveysGroups::model()->findByAttributes(['name' => $name]);
            if ($group !== null) {
                \Survey::model()->deleteAllByAttributes(['gsid' => $group->gsid]);
                \SurveysGroupsettings::model()->deleteAllByAttributes(['gsid' => $group->gsid]);
                $group->delete();
            }
        }
    }

    /**
     * @param string $name
     * @param int $ownerId
     * @return int the new group's gsid
     */
    private static function createGroup($name, $ownerId)
    {
        $group = new \SurveysGroups();
        $group->name = $name;
        $group->title = $name;
        $group->sortorder = 1;
        $group->owner_id = $ownerId;
        $group->created_by = $ownerId;
        if (!$group->save()) {
            throw new \Exception('Could not save survey group: ' . print_r($group->getErrors(), true));
        }

        $settings = new \SurveysGroupsettings();
        $settings->gsid = $group->gsid;
        $settings->setToInherit();
        if (!$settings->save()) {
            throw new \Exception('Could not save survey group settings: ' . print_r($settings->getErrors(), true));
        }

        return (int) $group->gsid;
    }

    /**
     * Regression test for the primary issue in Mantis #19077: SurveysGroups::search()
     * only faked a `DISTINCT` via the select string (which Yii's count-query builder
     * ignores) instead of setting CDbCriteria::$distinct, so the pagination/total
     * count re-ran the permission-check LEFT JOINs without deduplication and counted
     * one row per survey instead of one row per group.
     */
    public function testGroupCountIsNotInflatedBySurveyPermissionJoin()
    {
        $originalLoginId = \Yii::app()->session['loginID'] ?? null;
        try {
            \Yii::app()->session['loginID'] = self::$restrictedUserId;

            $model = new \SurveysGroups('search');
            $model->name = 'SGSearchOwnGroup';
            $dataProvider = $model->search();

            $this->assertEquals(
                1,
                $dataProvider->getTotalItemCount(),
                'Total item count must equal the number of matching survey groups (1), not the number of surveys inside them (3).'
            );
            $this->assertCount(1, $dataProvider->getData());
        } finally {
            \Yii::app()->session['loginID'] = $originalLoginId;
        }
    }

    /**
     * Confirms the permission filtering itself (not just the count) still excludes
     * groups the current user has no access to.
     */
    public function testRestrictedUserCannotSeeOtherUsersGroup()
    {
        $originalLoginId = \Yii::app()->session['loginID'] ?? null;
        try {
            \Yii::app()->session['loginID'] = self::$restrictedUserId;

            $model = new \SurveysGroups('search');
            $model->name = 'SGSearchOtherGroup';
            $dataProvider = $model->search();

            $this->assertEquals(0, $dataProvider->getTotalItemCount());
            $this->assertCount(0, $dataProvider->getData());
        } finally {
            \Yii::app()->session['loginID'] = $originalLoginId;
        }
    }

    /**
     * Regression test for the secondary issue reported in Mantis #19077: the group
     * dropdown used to assign a survey to a group is built from
     * SurveysGroups::getSurveyGroupsList(), which must only list groups the current,
     * non-superadmin user actually has access to.
     */
    public function testSurveyGroupsListOnlyIncludesPermittedGroups()
    {
        $originalLoginId = \Yii::app()->session['loginID'] ?? null;
        try {
            \Yii::app()->session['loginID'] = self::$restrictedUserId;

            $list = \SurveysGroups::getSurveyGroupsList();

            $this->assertArrayHasKey(self::$ownGroupGsid, $list);
            $this->assertArrayNotHasKey(self::$otherGroupGsid, $list);
        } finally {
            \Yii::app()->session['loginID'] = $originalLoginId;
        }
    }

    /**
     * Sanity check that the fix does not change behaviour for users with full global
     * read access (e.g. superadmin), whose permission criteria has no joins and was
     * therefore never affected by the original bug.
     */
    public function testGroupCountIsCorrectForSuperAdmin()
    {
        $originalLoginId = \Yii::app()->session['loginID'] ?? null;
        try {
            \Yii::app()->session['loginID'] = 1;

            $model = new \SurveysGroups('search');
            $model->name = 'SGSearchOwnGroup';
            $dataProvider = $model->search();

            $this->assertEquals(1, $dataProvider->getTotalItemCount());
        } finally {
            \Yii::app()->session['loginID'] = $originalLoginId;
        }
    }
}
