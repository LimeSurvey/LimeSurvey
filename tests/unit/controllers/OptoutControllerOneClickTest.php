<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;

/**
 * Tests for the one click unsubscribe endpoint (RFC 8058) of OptoutController.
 */
class OptoutControllerOneClickTest extends TestBaseClass
{
    /** @var array Original $_SERVER, $_GET and $_POST values */
    private $originalGlobals;

    /**
     * Import a survey with participants.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.controllers.OptoutController', true);
        $filename = self::$surveysFolder . '/limesurvey_survey_InviteParticipantsTest.lsa';
        self::importSurvey($filename);
    }

    /**
     * Save the request globals.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->originalGlobals = [$_SERVER, $_GET, $_POST];
    }

    /**
     * Restore the request globals.
     * @return void
     */
    protected function tearDown(): void
    {
        [$_SERVER, $_GET, $_POST] = $this->originalGlobals;
        parent::tearDown();
    }

    /**
     * A one click POST opts the participant out of the survey without confirmation.
     * @return void
     */
    public function testOneClickPostOptsOut()
    {
        $token = $this->getToken(1);
        $this->assertFalse($token->optOutStatus);

        $this->sendPost($token->token, ['List-Unsubscribe' => 'One-Click']);

        $token->refresh();
        $this->assertTrue($token->optOutStatus);
    }

    /**
     * A POST without the one click body is rejected and does not opt the participant out.
     * @return void
     */
    public function testPostWithoutOneClickBodyIsRejected()
    {
        $token = $this->getToken(2);
        $this->assertFalse($token->optOutStatus);

        try {
            $this->sendPost($token->token, ['List-Unsubscribe' => 'Something']);
            $this->fail('Expected a CHttpException');
        } catch (\CHttpException $exception) {
            $this->assertSame(400, $exception->statusCode);
        }

        $token->refresh();
        $this->assertFalse($token->optOutStatus);
    }

    /**
     * Get a participant of the imported survey.
     * @param int $tid
     * @return \Token
     */
    private function getToken($tid)
    {
        return \Token::model(self::$surveyId)->findByAttributes(['tid' => $tid]);
    }

    /**
     * Send a POST request to optout/oneclick for the given token.
     * @param string $token
     * @param array<string, string> $body
     * @return void
     */
    private function sendPost($token, array $body)
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = ['surveyid' => (string) self::$surveyId, 'token' => $token];
        $_POST = $body;
        $controller = new \OptoutController('optout');
        $controller->actiononeclick();
    }
}
