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
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        /** @var integer the only group id */
        $testGroupId = self::$testSurvey->groups[0]->gid;
        $questionFile = self::$surveysFolder . '/limesurvey_question_import_question_test.lsq';
        $question = base64_encode(file_get_contents($questionFile));
        /* must return integer */
        $result = $this->handler->import_question(
            $sessionKey,
            self::$surveyId,
            $testGroupId,
            $question,
            'lsq',
            'N',
            'QNewTitle'
        );
        $this->assertIsInt($result, 'There was an error importing a question with a code that already exists and new title is set.');
        /* Validate is set */
        $oQuestion = \Question::model()->find(
            "qid = :qid",
            array(':qid' => $result)
        );
        $this->assertNotEmpty($oQuestion);
        $this->assertEquals('QNewTitle', $oQuestion->title);
        /* must return array */
        $result = $this->handler->import_question(
            $sessionKey,
            self::$surveyId,
            $testGroupId,
            $question,
            'lsq',
            'N',
            'QNewTitle'
        );
        $this->assertIsArray($result, 'There was an error importing a question set a code that already exists.');
    }

    /**
     * Importing a question with a question code that already exists.
     * But set a new title
     */
    public function testImportQuestionWithSetTextAndHelp()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        /** @var integer the only group id */
        $testGroupId = self::$testSurvey->groups[0]->gid;
        $questionFile = self::$surveysFolder . '/limesurvey_question_import_question_test.lsq';
        $question = base64_encode(file_get_contents($questionFile));
        $result = $this->handler->import_question(
            $sessionKey,
            self::$surveyId,
            $testGroupId,
            $question,
            'lsq',
            'N',
            'QNewTitle2', // new code
            'QNewText', // new quetsion text (all i10n)
            'QNewHelp' // new quetsion help (all i10n)
        );

        $this->assertIsInt($result, 'There was an error importing a question with a code that already exists and new title is set when set text and help.');
        $oQuestionL10n = \QuestionL10n::model()->find(
            "qid = :qid and language = :language",
            array(':qid' => $result, ':language' => "en")
        );
        $this->assertNotEmpty($oQuestionL10n);
        $this->assertEquals('QNewText', $oQuestionL10n->question);
        $this->assertEquals('QNewHelp', $oQuestionL10n->help);

    }
}
