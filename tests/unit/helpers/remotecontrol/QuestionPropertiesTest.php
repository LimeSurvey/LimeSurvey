<?php

namespace ls\tests;

/**
 * Tests for the GititSurvey remote API.
 */
class QuestionGroupPropertiesTest extends BaseTest
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
     * Testing that question data is properly set
     * in the questions table.
     */
    public function testSetBasicQuestionProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = self::$testSurvey->getAllQuestions()[0];

        $questionData = array(
            'title' => 'QT',
            'mandatory' => 'Y',
        );

        $result = $this->handler->set_question_properties($sessionKey, $question->qid, $questionData, 'en');

        $this->assertArrayHasKey('title', $result, 'The question title should have been modified.');
        $this->assertArrayHasKey('mandatory', $result, 'The question mandatory attribute should have been modified.');
        $this->assertSame($result['title'], true, 'The question title should have been modified.');
        $this->assertSame($result['mandatory'], true, 'The question mandatory attribute should have been modified.');

        $question->refresh();

        $this->assertSame('QT', $question->title, 'The title was not changed.');
        $this->assertSame('Y', $question->mandatory, 'The mandatory question attribute was not changed.');
    }

    /**
     * Testing that question data is properly set
     * in the question_I10ns table.
     */
    public function testSetLanguageQuestionProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = self::$testSurvey->getAllQuestions()[0];

        // Setting question texts in English (old style).
        $enQuestionData = array(
            'question' => 'A new question',
            'help' => 'A new help text',
            'language' => 'en'
        );

        $result = $this->handler->set_question_properties($sessionKey, $question->qid, $enQuestionData, 'en');

        $this->assertArrayHasKey('questionl10ns', $result, 'The question properties in English should have been modified.');
        $this->assertArrayHasKey('en', $result['questionl10ns'], 'The question properties in English should have been modified.');
        $this->assertArrayHasKey('question', $result['questionl10ns']['en'], 'The question text should have been modified.');
        $this->assertArrayHasKey('help', $result['questionl10ns']['en'], 'The help text should have been modified.');

        $enQuestionData = \QuestionL10n::model()->findByAttributes(array('qid' => $question->qid, 'language' => 'en'));

        $this->assertSame('A new question', $enQuestionData->question, 'The question text was not changed');
        $this->assertSame('A new help text', $enQuestionData->help, 'The help text was not changed');

        // Setting question text in German (New style)
        $deQuestionData['questionl10ns']['de'] = [
            'question' => 'Der neue deutsche Fragen',
            'help' => 'Die neue deutsche Hilfstext',
        ];

        $result = $this->handler->set_question_properties($sessionKey, $question->qid, $deQuestionData, 'de');

        $this->assertArrayHasKey('questionl10ns', $result, 'The question properties in German should have been modified.');
        $this->assertArrayHasKey('de', $result['questionl10ns'], 'The question properties in German should have been modified.');
        $this->assertArrayHasKey('question', $result['questionl10ns']['de'], 'The question text should have been modified.');
        $this->assertArrayHasKey('help', $result['questionl10ns']['de'], 'The help text should have been modified.');

        $deQuestionData = \QuestionL10n::model()->findByAttributes(array('qid' => $question->qid, 'language' => 'de'));

        $this->assertSame('Der neue deutsche Fragen', $deQuestionData->question, 'The question text was not changed');
        $this->assertSame('Die neue deutsche Hilfstext', $deQuestionData->help, 'The help text was not changed');
    }
}
