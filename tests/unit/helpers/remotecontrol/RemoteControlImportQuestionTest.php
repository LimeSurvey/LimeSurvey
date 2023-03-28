<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlImportQuestionTest extends BaseTest
{
    /**
     * Setup.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        /** @var string */
        $filename = self::$surveysFolder . '/limesurvey_survey_import_question_test.lss';
        self::importSurvey($filename);
    }

    /**
     * Importing a question with a question code that does not previously exist.
     */
    public function testImportQuestionWithUniqueQuestionCode()
    {
        $handler = $this->handler;
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        /** @var integer the only group id */
        $testGroupId = self::$testSurvey->groups[0]->gid;

        // Attempt Importing Question
        $questionFile = self::$surveysFolder . '/limesurvey_question_import_question_test_II.lsq';
        $question = base64_encode(file_get_contents($questionFile));
        $result = $this->handler->import_question($sessionKey, self::$surveyId, $testGroupId, $question, 'lsq');
        $this->assertIsInt($result, 'There was an error importing a question with a code that did not already exists.');
    }

    /**
     * Importing a question with a question code that already exists.
     */
    public function testImportQuestionWithRepeatedQuestionCode()
    {
        $handler = $this->handler;
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        /** @var integer the only group id */
        $testGroupId = self::$testSurvey->groups[0]->gid;

        // Attempt Importing Question
        $questionFile = self::$surveysFolder . '/limesurvey_question_import_question_test.lsq';
        $question = base64_encode(file_get_contents($questionFile));
        $result = $this->handler->import_question($sessionKey, self::$surveyId, $testGroupId, $question, 'lsq');

        $this->assertIsArray($result, 'There was an error importing a question with a code that already exists.');
    }

    /**
     * Importing a question with a question code that already exists.
     * But set a new title
     */
    public function testImportQuestionWithRepeatedQuestionCodeSetNew()
    {
        $handler = $this->handler;
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        /** @var integer the only group id */
        $testGroupId = self::$testSurvey->groups[0]->gid;
        $questionFile = self::$surveysFolder . '/limesurvey_question_import_question_test.lsq';
        $question = base64_encode(file_get_contents($questionFile));
        $result = $this->handler->import_question($sessionKey, self::$surveyId, $testGroupId, $question, 'lsq', 'N', 'QNewTitle');

        $this->assertIsInt($result, 'There was an error importing a question with a code that already exists and new title is set.');
    }
}
