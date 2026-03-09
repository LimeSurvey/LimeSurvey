<?php

namespace ls\tests;

/**
 * Check filterxsshtml_forcedall and relatyed settings
 * @since 6.17.0
 * @group settings
 */
class XssAndScriptForSuperadminCheckTest extends TestBaseClass
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
        self::$userId = \User::insertUser($username, $password, 'Test superadmin for XSS', 1, 'superadmin@example.org');
        \Permission::model()->setGlobalPermission(self::$userId, 'superadmin', array('read_p'));
        \Permission::model()->setGlobalPermission(self::$userId, 'auth_db', array('read_p'));
    }

    /**
     * @return void
     */
    public function testXssOnAndScriptOffSuperadmin()
    {
        App()->setConfig('filterxsshtml_forcedall', true);
        App()->setConfig('disablescriptwithxss', true);
        App()->setConfig('filterxsshtml_enablescript', ''); // Be sure to be default
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals("Question:", $questionl10n->question);
        $this->assertEquals("Help:", $questionl10n->help);
        $this->assertEquals("", $questionl10n->script);
        self::$testSurvey->delete();
        /* reset to default */
        App()->setConfig('filterxsshtml_forcedall', false);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', false);
    }

    /**
     * @return void
     */
    public function testXssOnAndScriptOnSuperadmin()
    {
        App()->setConfig('filterxsshtml_forcedall', true);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', true);
        App()->setConfig('disablescriptwithxss', true);
        App()->setConfig('filterxsshtml_enablescript', 'superadmin');
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals("Question:", $questionl10n->question);
        $this->assertEquals("Help:", $questionl10n->help);
        $this->assertEquals("alert('script');", $questionl10n->script);
        self::$testSurvey->delete();
        /* reset to default */
        App()->setConfig('filterxsshtml_forcedall', false);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', false);
        App()->setConfig('filterxsshtml_enablescript', '');
    }

    /**
     * @return void
     */
    public function testXssOffForcedSuperAdmin()
    {
        App()->setConfig('filterxsshtml_forcedall', true);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', true);
        /* Keep forced super admin */
        $keepForcedsuperadmin = App()->getConfig('forcedsuperadmin');
        /* Check as forcedsuperadmin */
        App()->setConfig('forcedsuperadmin', [self::$userId]);
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals("Question:<script>alert('question');</script>", $questionl10n->question);
        $this->assertEquals("Help:<script>alert('help');</script>", $questionl10n->help);
        $this->assertEquals("alert('script');", $questionl10n->script);
        self::$testSurvey->delete();
        /* reset to default */
        App()->setConfig('filterxsshtml_forcedall', false);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', false);
        App()->setConfig('forcedsuperadmin', $keepForcedsuperadmin);
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
