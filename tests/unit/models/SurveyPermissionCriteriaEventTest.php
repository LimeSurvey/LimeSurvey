<?php

namespace ls\tests;

/**
 * The getSurveyPermissionCriteria event lets plugins extend the surveys a user can list (#16459).
 */
class SurveyPermissionCriteriaEventTest extends TestBaseClass
{
    /** @var \User User without any permission */
    private $user;

    /** @var \Survey Survey the plugin grants to the user */
    private $grantedSurvey;

    /** @var \Survey Survey the user has no access to */
    private $otherSurvey;

    /** @var \SurveyPermissionCriteriaPlugin */
    private $plugin;

    /**
     * Creates a user without permissions and two surveys owned by the admin.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Yii::app()->session['loginID'] = 1;
        $userName = 'perm_criteria_' . \Yii::app()->securityManager->generateRandomString(8);
        $this->user = self::createUserWithPermissions([
            'users_name' => $userName,
            'full_name' => $userName,
            'email' => $userName . '@example.org',
            'lang' => 'auto',
            'password' => 'testpassword123',
        ]);
        $this->grantedSurvey = \Survey::model()->insertNewSurvey(['owner_id' => 1]);
        $this->otherSurvey = \Survey::model()->insertNewSurvey(['owner_id' => 1]);

        require_once __DIR__ . '/../../data/plugins/SurveyPermissionCriteriaPlugin.php';
        $this->plugin = new \SurveyPermissionCriteriaPlugin(App()->getPluginManager(), 0);
        $this->plugin->init();
    }

    /**
     * Unsubscribes the plugin and deletes the test surveys and user.
     * @return void
     */
    protected function tearDown(): void
    {
        App()->getPluginManager()->unsubscribe($this->plugin, 'getSurveyPermissionCriteria');
        \Yii::app()->session['loginID'] = 1;
        foreach ([$this->grantedSurvey, $this->otherSurvey] as $survey) {
            if ($survey) {
                $survey->delete();
            }
        }
        if ($this->user) {
            \Permission::model()->deleteAllByAttributes(['uid' => $this->user->uid]);
            $this->user->delete();
        }
        \Survey::model()->resetCache();
        parent::tearDown();
    }

    /**
     * Surveys added by the plugin are listed by the permission scope, other surveys stay hidden.
     * @return void
     */
    public function testPluginExtendsPermissionScope()
    {
        $uid = (int) $this->user->uid;
        $this->assertSame([], $this->getPermittedSurveyIds($uid));

        $this->plugin->grantedSurveyIds = [$this->grantedSurvey->sid];
        $this->assertSame([(int) $this->grantedSurvey->sid], $this->getPermittedSurveyIds($uid));
        $this->assertSame(['userid' => $uid, 'hasGlobalRead' => false], $this->plugin->lastEvent);
    }

    /**
     * Surveys added by the plugin are listed and counted in the survey list.
     * @return void
     */
    public function testPluginExtendsSurveyList()
    {
        $this->plugin->grantedSurveyIds = [$this->grantedSurvey->sid];
        \Yii::app()->session['loginID'] = (int) $this->user->uid;

        $dataProvider = (new \Survey('search'))->search();
        $listedIds = array_map('intval', $dataProvider->getKeys());
        $this->assertContains((int) $this->grantedSurvey->sid, $listedIds);
        $this->assertNotContains((int) $this->otherSurvey->sid, $listedIds);
        $this->assertSame(1, (int) $dataProvider->getTotalItemCount());
    }

    /**
     * Users with global surveys read permission get the event flagged, their criteria stays unrestricted.
     * @return void
     */
    public function testGlobalReadUserIsFlagged()
    {
        $this->plugin->grantedSurveyIds = [$this->grantedSurvey->sid];
        $surveyIds = $this->getPermittedSurveyIds(1);
        $this->assertContains((int) $this->grantedSurvey->sid, $surveyIds);
        $this->assertContains((int) $this->otherSurvey->sid, $surveyIds);
        $this->assertSame(['userid' => 1, 'hasGlobalRead' => true], $this->plugin->lastEvent);
    }

    /**
     * Ids of the test surveys found with the permission scope for the given user.
     * @param int $uid
     * @return int[]
     */
    private function getPermittedSurveyIds($uid)
    {
        $criteria = new \CDbCriteria();
        $criteria->addInCondition('t.sid', [$this->grantedSurvey->sid, $this->otherSurvey->sid]);
        $surveys = \Survey::model()->permission($uid)->findAll($criteria);
        $surveyIds = array_map(function ($survey) {
            return (int) $survey->sid;
        }, $surveys);
        sort($surveyIds);
        return $surveyIds;
    }
}
