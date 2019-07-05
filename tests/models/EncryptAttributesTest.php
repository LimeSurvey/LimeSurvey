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
        // Get our token.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertNotEmpty($tokens);
        $this->assertCount(1, $tokens);
        $token = $tokens[0];

        // Change lastname.
        $token->lastname = 'last';
        $token->encryptSave();

        // Load token and decrypt.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $token->decrypt();
        $this->assertEquals('last', $token->lastname);

        // Test the omitting decrypt() works.
        $tokens = \TokenDynamic::model(self::$surveyId)->findAll();
        $this->assertCount(1, $tokens);
        $token = $tokens[0];
        $this->assertNotEquals('last', $token->lastname);
    }
}
