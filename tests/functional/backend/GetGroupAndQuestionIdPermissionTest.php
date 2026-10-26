<?php

namespace ls\tests;

/**
 * Check permission on condition.
 * Usage of LSYii_Application::getQuestionId() and check with LSYii_Application::getSurveyId();
 * @since 2026-06-10
 * @group security
 */

class GetGroupAndQuestionIdPermissionTest extends TestBaseClassWeb
{
    /** @var integer $userId */
    private static $userId;
    /** @var string $username */
    private static $username;
    /** @var integer $password */
    private static $password;

    protected static $superadminSurveyId;
    /** @var  \Survey */
    protected static $superadminSurvey;


    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        /* Create an random user and login */
        self::$username = "test_" . \Yii::app()->securityManager->generateRandomString(10);
        self::$password = createPassword();
        $result = \User::insertUser(self::$username, self::$password, 'Test user for GetQuestionId', 1, 'user@example.org');
        if ($result instanceof \User) {
            self::fail('Failed to create user: ' . json_encode($result->getErrors()));
        }
        self::$userId = (int) $result;
        \Permission::model()->setGlobalPermission(self::$userId, 'surveys', array('create_p'));
        \Permission::model()->setGlobalPermission(self::$userId, 'auth_db', array('read_p'));
    }


    /**
     * Check permission by ID in condition function,
     * This check LSYii_Application::getQuestionId() and LSYii_Application::getGroupId() permission check
     * @return void
     */
    public function testPermissionOnCondition()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_CheckGetQuestionID.lss';
        /* Import first survey as superamin */
        self::importSurvey($surveyFile, 1);
        self::$superadminSurvey = self::$testSurvey;
        self::$superadminSurveyId = self::$surveyId;
        $superadminQuestions = $this->getAllSurveyQuestions();
        $questions = $this->getAllSurveyQuestions();
        $superadminQid = $questions['Q2']->qid;
        $superadminGid = $questions['Q2']->gid;
        /* Import second survey as userOId */
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        self::adminLogin(self::$username, self::$password);
        \Yii::app()->session['loginID'] = self::$userId;
        App()->user->setId(self::$userId);
        self::importSurvey($surveyFile, self::$userId);
        $questions = $this->getAllSurveyQuestions();
        $questionQ2 = $questions['Q2'];
        $qid = $questions['Q2']->qid;
        $gid = $questions['Q2']->gid;

        /* Check good url but survey without access */
        $url = $urlMan->createUrl('/admin/conditions/sa/index/subaction/editconditionsform', array('surveyid' => self::$superadminSurveyId, 'gid' => $superadminGid, 'qid' => $superadminQid));
        try {
            self::$webDriver->get($url);
            $title = trim(self::$webDriver->getTitle());
            // From SurveyCommonAction in 7.0.2 (2026-06-19)
            if ($title != "403: Forbidden") {
                $this->fail("User can set surveyId without permission (page title : " . $title . ")");
            }
        } catch (\CException $exception) {
            /* No exception throw whene error happen, keep it if test is updated in 7.0.2 (2026-06-19) */
            if ($exception->statusCode != 403) {
                /* throw the exception : must be a 403 */
                throw $exception;
            }
            // Continue : it's OK
        }
        /* Test with check surveyid with XSS disable , get a 404 error, in test and a 403 error in real */
        /* Check good url but survey with access but invalid qid */
        $url = $urlMan->createUrl('admin/conditions/sa/index/subaction/editconditionsform', array('surveyid' => self::$surveyId, 'gid' => $gid, 'qid' => $superadminQid));
        try {
            self::$webDriver->get($url);
            $title = trim(self::$webDriver->getTitle());
            if ($title != "400: Bad Request") {
                $this->fail("User can get question ID without permission hacking surveyid in url (page title : " . $title . ")");
            }
        } catch (\CException $exception) {
            /* No exception throw when error happen in 7.0.2 (2026-06-19), keep it if test is updated */
            if ($exception->statusCode != 400) {
                /* throw the exception : must be a 400 */
                throw $exception;
            }
            // Continue : it's OK
        }
        /* Check good url but survey with access valid qid but invalid gid*/
        $url = $urlMan->createUrl('admin/conditions/sa/index/subaction/editconditionsform', array('surveyid' => self::$surveyId, 'gid' => $superadminGid, 'qid' => $qid));
        try {
            self::$webDriver->get($url);
            $title = trim(self::$webDriver->getTitle());
            if ($title != "400: Bad Request") {
                $this->fail("User can get group ID without permission hacking surveyid in url (page title : " . $title . ")");
            }
        } catch (\CException $exception) {
            /* No exception throw when error happen in 7.0.2 (2026-06-19), keep it if test is updated */
            if ($exception->statusCode != 400) {
                /* throw the exception : must be a 400 */
                throw $exception;
            }
            // Continue : it's OK
        }
        /* Test with check group ID with XSS disable , get a 404 error, in test and a 403 error in real from SurveyCommonAction ? */
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        \User::model()->deleteByPk(self::$userId);
        \Yii::app()->session['loginID'] = 1;
        App()->user->setId(1);
        \Yii::app()->db->schema->refresh();
        self::$superadminSurvey->delete();
        parent::tearDownAfterClass();
    }
}
