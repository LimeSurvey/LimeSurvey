<?php

namespace ls\tests;

/**
 * @group api
 */
class XssAndScriptCheckTest extends TestBaseClassWeb
{
    /**
     * @var integer $userId
     */
    private static $userId;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        /* Create an random user and login */
        $username = "test_" . \Yii::app()->securityManager->generateRandomString(5);
        $password = createPassword();
        self::$userId = \User::insertUser($username, $password, 'Test user for XSS', 1, 'user@example.org');
        \Permission::model()->setGlobalPermission(self::$userId, 'surveys', array('create_p'));
        \Permission::model()->setGlobalPermission(self::$userId, 'auth_db', array('read_p'));
        self::adminLogin($username, $password);
    }

    /**
     * @return void
     */
    public function testXssOnAndScriptOn()
    {
        App()->setConfig('filterxsshtml', true);
        App()->setConfig('disablescriptwithxss', self::$userId);
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_XssAndScriptCheck.lss';
        /** @todo : set a controller here
         * for \LSYii_Validators->xssfilter (set to false if no constructor)
         **/
        $survey = self::importSurvey($surveyFile, false);
        $questions = $this->getAllSurveyQuestions();
        $questionl10n = \QuestionL10n::model()->find(
            "qid = :qid and language = :language",
            [":qid" => $questions['Q00']->qid, ":language" => 'en']
        );
        $this->assertEquals('Question:', $questionl10n->question);
        $this->assertEquals('', $questionl10n->script);
        $survey->delete();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        \User::model()->deleteByPk(self::$userId);
    }
}

