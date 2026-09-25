<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

use Participant;
use LimeSurvey\Models\Services\ParticipantBlocklistHandler;
use Survey;

/**
 * Tests for the ParticipantBlocklistHandler service class.
 */
class ParticipantBlocklistHandlerTest extends TestBaseClass
{
    /**
     * Test adding participant to blocklist
     *
     * @return void
     */
    public function testAddToBlocklist()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_archive_993688_participantBlocklist.lsa';
        self::importSurvey($filename);

        // Create participant in CPDB
        \Yii::app()->session['participantid'] = '["1"]';
        $copyResult = Participant::model()->copyToCentral(self::$surveyId, [], []);
        if (empty($copyResult['success'])) {
            throw new \Exception('Failed to copy participants to the CPDB.');
        }

        $token = \Token::model(self::$surveyId)->findByPk(1);

        // Add participant to blocklist
        $blocklistHandler = new ParticipantBlocklistHandler();
        $blocklistResult = $blocklistHandler->addToBlocklist($token);

        $this->assertTrue($blocklistResult->isBlocklisted());

        // Cleanup: Delete the participant from CPDB so it can be re-added in other tests
        $token->decrypt();
        if (!empty($token->participant_id)) {
            Participant::model()->deleteByPk($token->participant_id);
        }

        self::$testSurvey->delete();
        self::$testSurvey = null;
        Survey::model()->resetCache();
    }

    /**
     * Test removing participant from blocklist
     *
     * @return void
     */
    public function testRemoveFromBlocklist()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_archive_993688_participantBlocklist.lsa';
        self::importSurvey($filename);

        // Create participant in CPDB
        \Yii::app()->session['participantid'] = '["1"]';
        $copyResult = Participant::model()->copyToCentral(self::$surveyId, [], []);
        if (empty($copyResult['success'])) {
            throw new \Exception('Failed to copy participants to the CPDB.');
        }

        $token = \Token::model(self::$surveyId)->findByPk(1);

        // Add participant to blocklist
        $blocklistHandler = new ParticipantBlocklistHandler();
        $blocklistResult = $blocklistHandler->addToBlocklist($token);

        // Remove from blocklist
        $blocklistResult = $blocklistHandler->removeFromBlocklist($token);

        $this->assertFalse($blocklistResult->isBlocklisted());

        // Cleanup: Delete the participant from CPDB so it can be re-added in other tests
        $token->decrypt();
        if (!empty($token->participant_id)) {
            Participant::model()->deleteByPk($token->participant_id);
        }

        self::$testSurvey->delete();
        self::$testSurvey = null;
        Survey::model()->resetCache();
    }
}
