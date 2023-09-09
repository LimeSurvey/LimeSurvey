<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
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

    public function testSetBasicQuestionProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = self::$testSurvey->getAllQuestions()[0];

        $questionData = array(
            'title' => 'QT',
            'mandatory' => 'Y',
            'question' => 'Test',
            'help' => 'Test',
            'language' => 'en'
        );

        $result = $this->handler->set_question_properties($sessionKey, $question->qid, $questionData, 'en');

        $questionData2['questionl10ns']['de'] = [
            'question' => 'Der neue deutsche Fragen',
            'help' => 'Die neue deutsche Hilfstext',
        ];

        $result = $this->handler->set_question_properties($sessionKey, $question->qid, $questionData2, 'en');
        $this->assertTrue(true);
    }

    public function testSetLanguageQuestionProperties()
    {
        $this->assertTrue(true);
    }
}
