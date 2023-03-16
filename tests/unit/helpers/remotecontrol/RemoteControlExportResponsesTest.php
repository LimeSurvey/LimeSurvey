<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlExportResponsesTest extends BaseTest
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Yii::import('application.libraries.BigData', true);

        // Import survey
        $filename = self::$surveysFolder . '/survey_export_responses_with_tokens.lsa';
        self::importSurvey($filename);
    }

    /**
     * Export responses with tokens.
     */
    public function testExportResponsesWithTokens()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $result = $this->handler->export_responses($sessionKey, self::$surveyId, 'json');

        $this->assertNotNull($result);
        $responses = json_decode(file_get_contents($result->fileName), true);

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;

        $this->assertTrue(count($responses['responses']) === 2, 'Two responses should have been exported.');
        $this->assertArrayHasKey('token', $responses['responses'][0]);
        $this->assertArrayHasKey('token', $responses['responses'][1]);
    }
}
