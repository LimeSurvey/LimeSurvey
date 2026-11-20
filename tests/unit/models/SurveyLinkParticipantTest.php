<?php

namespace ls\tests;

use SurveyLink;
use Token;

/**
 * Tests for SurveyLink: The link between a central participant and a survey participant.
 */
class SurveyLinkParticipantTest extends TestBaseClass
{
    /** @var integer ID of the survey participant the links point to */
    private static $tokenId;

    /**
     * Import a survey and add a participant who got an invitation and completed the survey.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_143933.lss');
        Token::createTable(self::$surveyId);

        $token = Token::create(self::$surveyId);
        $token->token = 'surveylinktest';
        $token->sent = '2026-01-02 10:00';
        $token->completed = '2026-01-03 11:00';
        $token->save(false);
        self::$tokenId = $token->tid;
    }

    /**
     * The link reads the invitation, reminder and completion state from its survey participant.
     *
     * @return void
     */
    public function testLinkReadsTheStateOfItsSurveyParticipant()
    {
        // The dates are shown in the date format of the session
        \Yii::app()->session['dateformat'] = 6;
        $link = new SurveyLink();
        $link->survey_id = self::$surveyId;
        $link->token_id = self::$tokenId;

        $this->assertInstanceOf('Token', $link->getTokenModel());
        $this->assertSame('2026-01-03 11:00', $link->isSubmitted);
        $this->assertNotNull($link->lastInvited, 'The participant got an invitation.');
        $this->assertNull($link->lastReminded, 'The participant got no reminder.');
    }
}
