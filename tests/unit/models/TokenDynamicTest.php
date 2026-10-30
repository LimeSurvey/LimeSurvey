<?php

namespace ls\tests;

use TokenDynamic;
use PHPUnit\Framework\TestCase;

class TokenDynamicTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/survey_archive_265831.lsa';
        self::importSurvey($surveyFile);

        TokenDynamic::model(self::$surveyId);
    }

    public function testTokenCanBeUsedWithoutTokenAttribute()
    {
        $token = new TokenDynamic(self::$surveyId);
        $this->assertFalse($token->canBeUsed(), 'There is no token attribute, the token should not be able to be used.');
    }

    public function testTokenCanBeUsedWithCompletedAttributeUnset()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'completed' => '',
        );

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);

        $this->assertTrue($token->canBeUsed(), 'The completed attribute was not set, the token should be able to be used.');
    }

    public function testTokenCanBeUsedWithCompletedAttributeSetToNo()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'completed' => 'N',
        );

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);

        $this->assertTrue($token->canBeUsed(), 'The completed attribute was set to N, the token should be able to be used.');
    }

    public function testTokenCanBeUsedWithAllowEditAfterCompletionSetToNo()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'completed' => 'N',
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'N';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);

        $this->assertTrue($token->canBeUsed(), 'The survey allow edit after completion attribute was set to N, the token should be able to be used.');
    }

    public function testTokenCanBeUsedWithAllowEditAfterCompletionSetToYes()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'completed' => 'N',
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'Y';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);

        $this->assertTrue($token->canBeUsed(), 'The survey allow edit after completion attribute was set to Y, the token should be able to be used.');
    }

    public function testTokenCanBeUsedWithCompletedAttributeSetToYes()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'completed' => 'Y',
        );

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);

        $this->assertFalse($token->canBeUsed(), 'The completed attribute was set to Y, the token should not be able to be used.');
    }

    public function testCompletedTokenCanBeUsedWithAllowEditAfterCompletionSetToNo()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'completed' => 'Y',
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'N';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);

        $this->assertFalse($token->canBeUsed(), 'The survey allow edit after completion attribute was set to N, the token should not be able to be used.');
    }

    public function testCompletedTokenCanBeUsedWithAllowEditAfterCompletionSetToYes()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'completed' => 'Y',
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'Y';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);

        $this->assertFalse($token->canBeUsed(), 'The survey allow edit after completion attribute was set to Y, the token should be able be used.');
    }

    public function testTokenCanBeEmailedWithoutTokenAttribute()
    {
        $token = new TokenDynamic(self::$surveyId);
        $this->assertFalse($token->canBeEmailed(), 'There is no token attribute, the token should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithEmailStatusOk()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'emailstatus' => 'OK',
            'email' => 'user@example.com',
            'completed' => 'N',
            'usesleft' => 1,
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'N';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);
        $this->assertTrue($token->canBeEmailed(), 'Email status is ok, the token should be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithEmailStatusNotOk()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'emailstatus' => '',
            'email' => 'user@example.com',
            'completed' => 'N',
            'usesleft' => 1,
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'N';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);
        $this->assertFalse($token->canBeEmailed(), 'Email status is empty, the token should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedNoEmailSet()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'emailstatus' => 'OK',
            'completed' => 'N',
            'usesleft' => 1,
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'N';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);
        $this->assertFalse($token->canBeEmailed(), 'Email was not set, the token should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithCompletedAttributeSetToYes()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'emailstatus' => 'OK',
            'email' => 'user@example.com',
            'completed' => 'Y',
            'usesleft' => 1,
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'N';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);
        $this->assertFalse($token->canBeEmailed(), 'Completes was set to Y, the token should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithNoUsesLeft()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'emailstatus' => 'OK',
            'email' => 'user@example.com',
            'completed' => 'N',
            'usesleft' => 0,
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'N';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);
        $this->assertFalse($token->canBeEmailed(), 'No uses left, the token should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithSurveyAllowAfterCompletionSetToYes()
    {
        $tokenData = array(
            'token' => 'a-token-string',
            'emailstatus' => 'OK',
            'email' => 'user@example.com',
            'completed' => 'N',
            'usesleft' => 0,
        );

        self::$testSurvey->oOptions->alloweditaftercompletion = 'Y';

        $token = new TokenDynamic(self::$surveyId);
        $token->setAttributes($tokenData, false);
        $this->assertFalse($token->canBeEmailed(), 'Survey can be edited after completion, the token should not be able to be emailed.');
    }

    /**
     * The survey encrypts lastname and email, the duplicate check on import must still find the participant.
     */
    public function testCountByPlainAttributesWithEncryptedAttributes()
    {
        $model = TokenDynamic::model(self::$surveyId);
        $encryptedAttributes = $model->getAllEncryptedAttributes(self::$surveyId, 'TokenDynamic');
        $this->assertSame(['lastname', 'email'], array_values(array_intersect(['firstname', 'lastname', 'email'], $encryptedAttributes)));

        $tokens = [];
        try {
            $token = \Token::create(self::$surveyId);
            $token->firstname = 'Jane';
            $token->lastname = 'Doe';
            $token->email = 'jane.doe@example.com';
            $this->assertTrue($token->encryptSave(true));
            $tokens[] = $token;

            $otherToken = \Token::create(self::$surveyId);
            $otherToken->firstname = 'John';
            $otherToken->lastname = 'Doe';
            $otherToken->email = 'jane.doe@example.com';
            $this->assertTrue($otherToken->encryptSave(true));
            $tokens[] = $otherToken;

            $noLastnameToken = \Token::create(self::$surveyId);
            $noLastnameToken->firstname = 'Jane';
            $noLastnameToken->lastname = '';
            $noLastnameToken->email = 'jane.nolastname@example.com';
            $this->assertTrue($noLastnameToken->encryptSave(true));
            $tokens[] = $noLastnameToken;

            $this->assertSame(1, $model->countByPlainAttributes([
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'email' => 'jane.doe@example.com',
            ], $encryptedAttributes), 'The participant with the same encrypted attributes should be found.');
            $this->assertSame(0, $model->countByPlainAttributes([
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'email' => 'john.doe@example.com',
            ], $encryptedAttributes), 'A participant with another email should not be found.');
            $this->assertSame(1, $model->countByPlainAttributes([
                'firstname' => 'Jane',
                'lastname' => '',
                'email' => 'jane.nolastname@example.com',
            ], $encryptedAttributes), 'An empty encrypted value should be compared as it is saved.');
            $this->assertSame(0, $model->countByAttributes([
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'email' => 'jane.doe@example.com',
            ]), 'countByAttributes() should still compare the given values as they are.');
        } finally {
            foreach ($tokens as $token) {
                $token->delete();
            }
        }
    }
}
