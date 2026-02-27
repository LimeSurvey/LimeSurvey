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
    /**
     * Ensure core participant attributes (firstname, lastname, email) are created in the database
     *
     * @return void
     */
    private function ensureCoreParticipantAttributes()
    {
        $coreAttributes = ['firstname', 'lastname', 'email'];
        foreach ($coreAttributes as $attrName) {
            $existing = \ParticipantAttributeName::model()->findByAttributes([
                'defaultname' => $attrName,
                'core_attribute' => 'Y'
            ]);

            if (!$existing) {
                $attr = new \ParticipantAttributeName();
                $attr->attribute_type = 'TB';
                $attr->defaultname = $attrName;
                $attr->visible = 'TRUE';
                $attr->encrypted = 'N';
                $attr->core_attribute = 'Y';
                $attr->save();
            }
        }
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

        // Ensure CPDB core attributes exist
        $this->ensureCoreParticipantAttributes();

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

        // Ensure CPDB core attributes exist
        $this->ensureCoreParticipantAttributes();

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