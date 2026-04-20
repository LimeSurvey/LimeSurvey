<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class CheckQuestionPropertiesTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $filename = self::$surveysFolder . '/limesurvey_survey_remote_api_group_language.lss';
        self::importSurvey($filename);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * Testing that question data is properly get
     * in the questions table.
     */
    public function testCheckBasicQuestionProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = self::$testSurvey->getAllQuestions()[0];
        $result = $this->handler->get_question_properties($sessionKey, $question->qid);

        $this->assertArrayHasKey('title', $result, 'The question title exist.');
        $this->assertArrayHasKey('mandatory', $result, 'The question mandatory attribute exist.');
        $this->assertSame($result['title'], 'Q00');
        $this->assertSame($result['mandatory'], 'N');
    }

    /**
     * Testing that question language data is properly get
     * in the questions table.
     */
    public function testCheckL10nQuestionProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = self::$testSurvey->getAllQuestions()[0];
        $result = $this->handler->get_question_properties($sessionKey, $question->qid);

        $this->assertArrayHasKey('question', $result, 'The question text exist.');
        $this->assertArrayHasKey('help', $result, 'The question help exist.');
        $this->assertIsArray($result['questionl10ns'], 'The question l10n are included');
        $this->assertSame($result['question'], 'A first example question. Please answer this question:');
        $this->assertSame($result['help'], 'This is a question help text.');
    }
}
