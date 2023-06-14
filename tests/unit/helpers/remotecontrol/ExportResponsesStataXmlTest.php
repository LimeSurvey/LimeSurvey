<?php

namespace ls\tests;

/**
 * Tests for ExportSTATAxml.
 */
class ExportResponsesStataXmlTest extends BaseTest
{
    private static $pluginName = 'ExportSTATAxml';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

        // Activate it, if not already
        self::installAndActivatePlugin(self::$pluginName);

        // Import survey
        $filename = self::$surveysFolder . '/survey_export_responses_with_tokens.lsa';
        self::importSurvey($filename);
    }

    /**
     * Get responses in stataxml format.
     */
    public function testGetResponses()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $result = $this->handler->export_responses($sessionKey, self::$surveyId, 'stataxml');
        $this->assertNotNull($result);
    }
}
