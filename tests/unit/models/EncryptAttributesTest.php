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
     * Test token with 0 as value
     * No validation
     */
    public function testTokenCrypt0()
    {
        // Get our token.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertNotEmpty($tokens);
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();

        // Change attribute_1.
        $token->attribute_1 = '0';
        $token->encryptSave(false);

        // Load token and decrypt.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();
        $this->assertEquals('0', $token->attribute_1);

        // Test the omitting decrypt() works.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $this->assertNotEquals('0', $token->attribute_1);
    }

    /**
     * Test token with "" as value
     * No validation
     */
    public function testTokenCryptEmptyString()
    {
        // Get our token.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertNotEmpty($tokens);
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();

        // Change attribute_1.
        $token->attribute_1 = '';
        $token->encryptSave(false);

        // Load token and decrypt.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();
        $this->assertEquals('', $token->attribute_1);

        // Test the omitting decrypt works : "" is not cryted
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        /* "" is not crypted */
        $this->assertEquals('', $token->attribute_1);
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

    /**
     * Test response savec with 0
     * With validation
     */
    public function testResponseCrypt0()
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
        $response->$sgqa = "0";
        $response->encryptSave(true);

        // Load answer
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);
        $response = $responses[0];

        $answer = $response->$sgqa;
        $response->decrypt();
        $decryptedAnswer = $response->$sgqa;

        $this->assertEquals('0', $decryptedAnswer);
        $this->assertNotEquals('0', $answer);
    }

    /**
     * Test response saved with ""
     * With validation
     */
    public function testResponseCryptEmptyString()
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
        $response->$sgqa = "";
        $response->encryptSave(true);

        // Load answer
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);
        $response = $responses[0];

        $answer = $response->$sgqa;
        $response->decrypt();
        $decryptedAnswer = $response->$sgqa;

        $this->assertEquals('', $decryptedAnswer);
        /* "" is not crypted */
        $this->assertEquals('', $answer);
    }
}
