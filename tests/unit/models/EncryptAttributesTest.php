<?php

namespace ls\tests;

/**
 * @group encrypttion
 * @group enc
 */
class EncryptAttributesTest extends TestBaseClass
{
    /**
     * Setup before class.
     */
    public static function setupBeforeClass(): void
    {
        \Yii::import('application.helpers.globalsettings_helper', true);
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/survey_archive_265831.lsa';
        self::importSurvey($surveyFile);
    }
    
    /**
     * Test token without validation.
     */
    public function testTokenWithoutValidation()
    {
        // Get our token.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertNotEmpty($tokens);
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();

        // Change lastname.
        $token->lastname = 'last';
        $token->encryptSave(false);

        // Load token and decrypt.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();
        $this->assertEquals('last', $token->lastname);
        $this->assertEquals('foo@bar.com', $token->email);

        // Test the omitting decrypt() works.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $this->assertNotEquals('last', $token->lastname);
    }

    /**
     * Test token with validation.
     */
    public function testTokenWithValidation()
    {
        // Get our token.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertNotEmpty($tokens);
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();

        // Change lastname.
        $token->lastname = 'last';
        $token->encryptSave(true);

        // Load token and decrypt.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();
        $this->assertEquals('last', $token->lastname);
        $this->assertEquals('foo@bar.com', $token->email);

        // Test the omitting decrypt() works.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $this->assertNotEquals('last', $token->lastname);
    }

    /**
     * Test response.
     */
    public function testResponseWithoutValidation()
    {
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);
        $response = $responses[0];
        $response->decrypt();

        // Get questions.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }

        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['Q00']->qid;

        // Change answer
        $response->$sgqa = "New answer.";
        $response->encryptSave(false);

        // Load answer
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);
        $response = $responses[0];

        $answer = $response->$sgqa;
        $response->decrypt();
        $decryptedAnswer = $response->$sgqa;

        $this->assertEquals('New answer.', $decryptedAnswer);
        $this->assertNotEquals('New answer.', $answer);
    }

    /**
     * Test response.
     */
    public function testResponseWithValidation()
    {
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);
        $response = $responses[0];
        $response->decrypt();

        // Get questions.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }

        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['Q00']->qid;

        // Change answer
        $response->$sgqa = "New answer.";
        $response->encryptSave(true);

        // Load answer
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);
        $response = $responses[0];

        $answer = $response->$sgqa;
        $response->decrypt();
        $decryptedAnswer = $response->$sgqa;

        $this->assertEquals('New answer.', $decryptedAnswer);
        $this->assertNotEquals('New answer.', $answer);
    }
}
