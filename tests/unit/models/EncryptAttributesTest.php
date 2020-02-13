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
    public static function setupBeforeClass()
    {
        \Yii::import('application.helpers.globalsettings_helper', true);
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/survey_archive_265831.lsa';
        self::importSurvey($surveyFile);
    }
    
    /**
     * Test token.
     */
    public function testToken()
    {
        function generateRandomString($length = 10) {
            return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
        }
        
        $teststring = generateRandomString(128);

        // Get our token.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertNotEmpty($tokens);
        $this->assertCount(1, $tokens);
        $token = $tokens[0];

        // Change lastname.
        $token->lastname = $teststring;
        $token->encryptSave();

        // Load token and decrypt.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();
        $this->assertEquals($teststring, $token->lastname);

        // Test the omitting decrypt() works.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $this->assertNotEquals($teststring, $token->lastname);
    }

    /**
     * Test response.
     */
    public function testResponse()
    {
        $responses = \Response::model(self::$surveyId)->findAll();
        $this->assertCount(1, $responses);

        $response = $responses[0];

        // Get questions.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }

        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['Q00']->qid;

        $answer = $response->$sgqa;
        $response->decrypt();
        $decryptedAnswer = $response->$sgqa;

        $this->assertEquals('One answer.', $decryptedAnswer);
        $this->assertNotEquals('One answer.', $answer);
    }
}
