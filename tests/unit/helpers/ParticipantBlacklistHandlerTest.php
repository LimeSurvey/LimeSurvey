<?php

namespace ls\tests;

use Participant;
use LimeSurvey\Models\Services\ParticipantBlacklistHandler;
use Survey;

/**
 * Tests for the ParticipantBlacklistHandler service class.
 */
class ParticipantBlacklistHandlerTest extends TestBaseClass
{
    public static function setupBeforeClass(): void
    {
        if (YII_DEBUG) {
            self::markTestSkipped();
        }
        parent::setUpBeforeClass();
    }
    /**
     * Test adding participant to blocklist
     *
     * @return void
     */
    public function testAddToBlacklist()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_archive_993688_participantBlacklist.lsa';
        self::importSurvey($filename);

        // Create participant in CPDB
        \Yii::app()->session['participantid'] = 1;
        $copyResult = Participant::model()->copyToCentral(self::$surveyId, [], []);
        if (empty($copyResult['success'])) {
            throw new \Exception('Failed to copy participants to the CPDB.');
        }

        $token = \Token::model(self::$surveyId)->findByPk(1);

        // Add participant to blocklist
        $blacklistHandler = new ParticipantBlacklistHandler();
        $blacklistResult = $blacklistHandler->addToBlacklist($token);

        $this->assertTrue($blacklistResult->isBlacklisted());

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
        Survey::model()->resetCache();
    }

    /**
     * Test removing participant from blocklist
     *
     * @return void
     */
    public function testRemoveFromBlacklist()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_archive_993688_participantBlacklist.lsa';
        self::importSurvey($filename);

        // Create participant in CPDB
        \Yii::app()->session['participantid'] = 1;
        $copyResult = Participant::model()->copyToCentral(self::$surveyId, [], []);
        if (empty($copyResult['success'])) {
            throw new \Exception('Failed to copy participants to the CPDB.');
        }

        $token = \Token::model(self::$surveyId)->findByPk(1);

        // Add participant to blocklist
        $blacklistHandler = new ParticipantBlacklistHandler();
        $blacklistResult = $blacklistHandler->addToBlacklist($token);

        // Remove from blocklist
        $blacklistResult = $blacklistHandler->removeFromBlacklist($token);

        $this->assertFalse($blacklistResult->isBlacklisted());

        // Cleanup
        self::$testSurvey->delete();
        self::$testSurvey = null;
        Survey::model()->resetCache();
    }
}
