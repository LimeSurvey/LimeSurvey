<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlImportQuestionTest extends TestBaseClass
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    /**
     * Setup.
     *
     * @return void
     */
    public static function setupBeforeClass()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);

        parent::setupBeforeClass();

        self::$username = getenv('ADMINUSERNAME');
        if (!self::$username) {
            self::$username = 'admin';
        }

        self::$password = getenv('PASSWORD');
        if (!self::$password) {
            self::$password = 'password';
        }

        // Clear login attempts.
        $dbo = \Yii::app()->getDb();
        $query = sprintf('DELETE FROM {{failed_login_attempts}}');
        $dbo->createCommand($query)->execute();

        /** @var string */
        $filename = self::$surveysFolder . '/limesurvey_survey_import_question_test.lss';
        self::importSurvey($filename);
    }

    /**
     * Importing a question with a question code that does not previously exist.
     */
    public function testImportQuestionWithUniqueQuestionCode()
    {
        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );

        // Pickup group id of imported survey.
        // There is only one group
        $testGroupId = self::$testSurvey->groups[0]->gid;

        // Attempt Improting Question
        $questionFile = self::$surveysFolder . '/limesurvey_question_import_question_test_II.lsq';
        $question = base64_encode(file_get_contents($questionFile));
        $result = $handler->import_question($sessionKey, self::$surveyId, $testGroupId, $question, 'lsq');

        $this->assertInternalType('int', $result, 'There was an error importing a question with a code that did not already exists.');
    }

    /**
     * Importing a question with a question code that already exists.
     */
    public function testImportQuestionWithRepeatedQuestionCode()
    {
        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );

        // Pickup group id of imported survey.
        // There is only one group
        $testGroupId = self::$testSurvey->groups[0]->gid;

        // Attempt Improting Question
        $questionFile = self::$surveysFolder . '/limesurvey_question_import_question_test.lsq';
        $question = base64_encode(file_get_contents($questionFile));
        $result = $handler->import_question($sessionKey, self::$surveyId, $testGroupId, $question, 'lsq');
        
        $this->assertInternalType('int', $result, 'There was an error importing a question with a code that already exists.');
    }
}
