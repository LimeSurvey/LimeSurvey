<?php

namespace ls\tests;

/**
 * Tests for the GititSurvey remote API.
 */
class RemoteControlExportResponsesByTokenTest extends BaseTest
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

        // Import survey
        $filename = self::$surveysFolder . '/survey_export_responses_by_token_955579.lsa';
        self::importSurvey($filename);
    }

    /**
     * Export responses by token 'token2'.
     */
    public function testExportResponsesByToken2()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $result = $this->handler->export_responses_by_token($sessionKey, self::$surveyId, 'json', 'token2');
        $this->assertNotNull($result);

        $responses = json_decode(file_get_contents($result->fileName), true);
        $this->assertTrue(count($responses['responses']) === 1, 'One response exported was expected.');
        $this->assertTrue($responses['responses'][0]['token'] === 'token2', 'Response with token2 was expected');
    }

    /**
     * Export responses by token array ('token1', 'token2').
     */
    public function testExportResponsesByTokensArray()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $result = $this->handler->export_responses_by_token($sessionKey, self::$surveyId, 'json', array('token1', 'token2'));
        $this->assertNotNull($result);

        $responses = json_decode(file_get_contents($result->fileName), true);
        $nrresponses = count($responses['responses']);
        $this->assertTrue($nrresponses === 2, 'Two responses should have been exported. Found: ' . $nrresponses);
        $this->assertTrue($responses['responses'][0]['token'] === 'token1', 'Response with token1 was expected');
        $this->assertTrue($responses['responses'][1]['token'] === 'token2', 'Response with token2 was expected');
    }
 }
