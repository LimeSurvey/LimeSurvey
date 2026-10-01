<?php

namespace ls\tests;

/**
 * Tests for the List-Unsubscribe headers added by LimeMailer.
 */
class LimeMailerListUnsubscribeTest extends TestBaseClass
{
    /** @var string Token of the first participant of the imported survey */
    private static $token;

    /** @var string Host info of the request before the tests */
    private static $originalHostInfo;

    /**
     * Import a survey with participants and set a controller for url creation.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $filename = self::$surveysFolder . '/limesurvey_survey_InviteParticipantsTest.lsa';
        self::importSurvey($filename);
        \Yii::app()->setController(new DummyController('dummyid'));
        self::$token = \Token::model(self::$surveyId)->find()->token;
        self::$originalHostInfo = \Yii::app()->getRequest()->getHostInfo();
    }

    /**
     * Restore the original host info of the request.
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->getRequest()->setHostInfo(self::$originalHostInfo);
        parent::tearDownAfterClass();
    }

    /**
     * Use an HTTPS host by default, as required for one click unsubscribe.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Yii::app()->getRequest()->setHostInfo('https://example.org');
    }

    /**
     * An email whose template contains the OPTOUTURL placeholder gets both headers.
     * @return void
     */
    public function testHeadersAddedWithOptoutPlaceholder()
    {
        $mailer = $this->getTokenMailer('Click {OPTOUTURL} to opt out.');
        $headers = $this->getListUnsubscribeHeaders($mailer);

        $this->assertCount(1, $headers['List-Unsubscribe']);
        $this->assertStringStartsWith('<https://example.org', $headers['List-Unsubscribe'][0]);
        $this->assertStringContainsString(self::$token, $headers['List-Unsubscribe'][0]);
        $this->assertSame(['List-Unsubscribe=One-Click'], $headers['List-Unsubscribe-Post']);
    }

    /**
     * An email whose body already contains the opt-out url gets both headers.
     * @return void
     */
    public function testHeadersAddedWithOptoutUrlInBody()
    {
        $mailer = $this->getTokenMailer('');
        $mailer->Body = 'Opt out: ' . \Yii::app()->getController()->createAbsoluteUrl(
            '/optout/tokens',
            ['surveyid' => self::$surveyId, 'token' => self::$token]
        );
        $headers = $this->getListUnsubscribeHeaders($mailer);

        $this->assertCount(1, $headers['List-Unsubscribe']);
        $this->assertCount(1, $headers['List-Unsubscribe-Post']);
    }

    /**
     * An email without an opt-out link (e.g. registration or confirmation) gets no headers.
     * @return void
     */
    public function testNoHeadersWithoutOptoutLink()
    {
        $mailer = $this->getTokenMailer('Your survey link: {SURVEYURL}');
        $headers = $this->getListUnsubscribeHeaders($mailer);

        $this->assertSame([], $headers['List-Unsubscribe']);
        $this->assertSame([], $headers['List-Unsubscribe-Post']);
    }

    /**
     * Over plain HTTP only List-Unsubscribe is added, List-Unsubscribe-Post requires an HTTPS URI (RFC 8058).
     * @return void
     */
    public function testNoOneClickHeaderOverHttp()
    {
        \Yii::app()->getRequest()->setHostInfo('http://example.org');
        $mailer = $this->getTokenMailer('Click {OPTOUTURL} to opt out.');
        $headers = $this->getListUnsubscribeHeaders($mailer);

        $this->assertCount(1, $headers['List-Unsubscribe']);
        $this->assertStringStartsWith('<http://example.org', $headers['List-Unsubscribe'][0]);
        $this->assertSame([], $headers['List-Unsubscribe-Post']);
    }

    /**
     * Adding the headers twice on the same instance does not duplicate them.
     * @return void
     */
    public function testHeadersNotDuplicated()
    {
        $mailer = $this->getTokenMailer('Click {OPTOUTURL} to opt out.');
        $this->getListUnsubscribeHeaders($mailer);
        $headers = $this->getListUnsubscribeHeaders($mailer);

        $this->assertCount(1, $headers['List-Unsubscribe']);
        $this->assertCount(1, $headers['List-Unsubscribe-Post']);
    }

    /**
     * Get a mailer set up for the test participant with the given raw body.
     * @param string $rawBody
     * @return \LimeMailer
     */
    private function getTokenMailer($rawBody)
    {
        $mailer = \LimeMailer::getInstance(\LimeMailer::ResetComplete);
        $mailer->setSurvey(self::$surveyId);
        $mailer->setToken(self::$token);
        $mailer->rawBody = $rawBody;
        $mailer->Body = $rawBody;
        return $mailer;
    }

    /**
     * Run the private header method and return the List-Unsubscribe header values by name.
     * @param \LimeMailer $mailer
     * @return array<string, string[]>
     */
    private function getListUnsubscribeHeaders(\LimeMailer $mailer)
    {
        $method = new \ReflectionMethod(\LimeMailer::class, 'addListUnsubscribeHeaders');
        $method->setAccessible(true);
        $method->invoke($mailer);

        $headers = ['List-Unsubscribe' => [], 'List-Unsubscribe-Post' => []];
        foreach ($mailer->getCustomHeaders() as $header) {
            if (array_key_exists($header[0], $headers)) {
                $headers[$header[0]][] = $header[1];
            }
        }
        return $headers;
    }
}
