<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * A quota exit must mark the participant as 'Q' and consume one use of the access code,
 * also once the response has been started (tokenused in session).
 */
class SubmitTokensQuotaExitTest extends TestBaseClass
{
    /**
     * Import and activate the quota survey and create its participant table
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->activateSurvey(self::$surveyId);
        \Token::createTable(self::$surveyId);
        \Yii::app()->db->schema->refresh();
    }

    /**
     * Reset session data
     */
    public function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * Quota exit after the response was started decreases the uses left
     */
    public function testQuotaExitConsumesOneUse()
    {
        $token = \Token::create(self::$surveyId);
        $token->firstname = 'quota';
        $token->token = 'quotaexit3';
        $token->usesleft = 3;
        $this->assertTrue($token->encryptSave(), json_encode($token->errors));

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        $_SESSION['responses_' . self::$surveyId] = [
            's_lang' => 'en',
            'token' => 'quotaexit3',
            'tokenused' => 'quotaexit3',
        ];

        \submittokens(true);

        $token = \Token::model(self::$surveyId)->findByAttributes(['token' => 'quotaexit3']);
        $this->assertSame('Q', $token->completed);
        $this->assertEquals(2, $token->usesleft);
    }
}
