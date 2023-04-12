<?php

namespace ls\tests;

/**
 * @group api
 */
class XssAndScriptCheckTest extends TestBaseClass
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
        $username = "test_" . \Yii::app()->securityManager->generateRandomString(8);
        $password = createPassword();
        self::$userId = \User::insertUser($username, $password, 'Test user for XSS', 1, 'user@example.org');
        \Permission::model()->setGlobalPermission(self::$userId, 'surveys', array('create_p'));
        \Permission::model()->setGlobalPermission(self::$userId, 'auth_db', array('read_p'));
    }

    /**
     * @return void
     */
    public function testXssOnAndScriptOn()
    {
        App()->setConfig('filterxsshtml', true);
        App()->setConfig('disablescriptwithxss', true);
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals("Question:", $questionl10n->question);
        $this->assertEquals("Help:", $questionl10n->help);
        $this->assertEquals("", $questionl10n->script);
        self::$testSurvey->delete();
    }

    /**
     * @return void
     */
    public function testXssOnAndScriptOff()
    {
        App()->setConfig('filterxsshtml', true);
        App()->setConfig('disablescriptwithxss', false);
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals("Question:", $questionl10n->question);
        $this->assertEquals("Help:", $questionl10n->help);
        $this->assertEquals("alert('script');", $questionl10n->script);
        self::$testSurvey->delete();
    }

    /**
     * @return void
     */
    public function testXssOff()
    {
        App()->setConfig('filterxsshtml', false);
        // No need to set disablescriptwithxss : auto false
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals("Question:<script>alert('question');</script>", $questionl10n->question);
        $this->assertEquals("Help:<script>alert('help');</script>", $questionl10n->help);
        $this->assertEquals("alert('script');", $questionl10n->script);
        self::$testSurvey->delete();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        \User::model()->deleteByPk(self::$userId);
    }

    /**
     * Import the survey and get the questioni10n
     * return \QuestionL10n
     */
    private function importSurveyAndgetQuestionI10n()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_XssAndScriptCheck.lss';
        self::importSurvey($surveyFile, self::$userId);
        $questions = $this->getAllSurveyQuestions();
        return  \QuestionL10n::model()->find(
            "qid = :qid and language = :language",
            [":qid" => $questions['Q00']->qid, ":language" => 'en']
        );
    }
}
