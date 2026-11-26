<?php

namespace ls\tests;

use TokenDynamic;

class TokenDynamicTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/survey_archive_265831.lsa';
        self::importSurvey($surveyFile);

        TokenDynamic::model(self::$surveyId);
    }

    /**
     * Creates an unsaved participant with the given attributes.
     * The "allow edit after completion" setting is set on the survey instance that the participant uses.
     * @param array $attributes Participant attributes
     * @param bool $allowEditAfterCompletion Value of the survey setting "Allow editing after completion"
     * @return TokenDynamic
     */
    private function createParticipant(array $attributes, bool $allowEditAfterCompletion = false): TokenDynamic
    {
        $participant = new TokenDynamic(self::$surveyId);
        $participant->setAttributes($attributes, false);
        $participant->survey->oOptions->alloweditaftercompletion = $allowEditAfterCompletion ? 'Y' : 'N';
        return $participant;
    }

    /**
     * Returns the attributes of a participant that can be emailed
     * @param array $overrides Attributes that replace the defaults
     * @return array
     */
    private function getEmailableAttributes(array $overrides = []): array
    {
        return array_merge(
            [
                'token' => 'a-token-string',
                'emailstatus' => 'OK',
                'email' => 'user@example.com',
                'completed' => 'N',
                'usesleft' => 1,
            ],
            $overrides
        );
    }

    public function testTokenCanBeUsedWithoutTokenAttribute()
    {
        $participant = $this->createParticipant([]);
        $this->assertFalse($participant->canBeUsed(), 'There is no access code, the participant should not be able to enter the survey.');
    }

    public function testTokenCanBeUsedWithCompletedAttributeUnset()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => '', 'usesleft' => 1]);
        $this->assertTrue($participant->canBeUsed(), 'The completed attribute was not set, the participant should be able to enter the survey.');
    }

    public function testTokenCanBeUsedWithCompletedAttributeSetToNo()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => 'N', 'usesleft' => 1]);
        $this->assertTrue($participant->canBeUsed(), 'The completed attribute was set to N, the participant should be able to enter the survey.');
    }

    public function testTokenCanBeUsedWithAllowEditAfterCompletionSetToYes()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => 'N', 'usesleft' => 1], true);
        $this->assertTrue($participant->canBeUsed(), 'Editing after completion is allowed, the participant should be able to enter the survey.');
    }

    public function testCompletedTokenCanBeUsedWithAllowEditAfterCompletionSetToNo()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => 'Y', 'usesleft' => 0]);
        $this->assertFalse($participant->canBeUsed(), 'The participant completed the survey and editing after completion is not allowed, the participant should not be able to enter the survey.');
    }

    public function testCompletedTokenCanBeUsedWithAllowEditAfterCompletionSetToYes()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => 'Y', 'usesleft' => 0], true);
        $this->assertTrue($participant->canBeUsed(), 'Editing after completion is allowed, the participant should be able to enter the survey again.');
    }

    public function testDateCompletedTokenCanBeUsedWithAllowEditAfterCompletionSetToNo()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => '2026-01-01 10:00', 'usesleft' => 0]);
        $this->assertFalse($participant->canBeUsed(), 'The participant completed the survey (date stamp) and editing after completion is not allowed, the participant should not be able to enter the survey.');
    }

    public function testScreenedOutTokenCanBeUsedWithAllowEditAfterCompletionSetToNo()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => 'Q', 'usesleft' => 1]);
        $this->assertFalse($participant->canBeUsed(), 'The participant was screened out by a quota, the participant should not be able to enter the survey.');
    }

    public function testTokenCanBeUsedWithNoUsesLeft()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => 'N', 'usesleft' => 0]);
        $this->assertFalse($participant->canBeUsed(), 'There are no uses left, the participant should not be able to enter the survey.');
    }

    public function testTokenCanBeUsedWithNoUsesLeftAndAllowEditAfterCompletionSetToYes()
    {
        $participant = $this->createParticipant(['token' => 'a-token-string', 'completed' => 'N', 'usesleft' => 0], true);
        $this->assertTrue($participant->canBeUsed(), 'Editing after completion is allowed, the uses left should not matter.');
    }

    public function testTokenCanBeUsedInsideValidityPeriod()
    {
        $participant = $this->createParticipant([
            'token' => 'a-token-string',
            'completed' => 'N',
            'usesleft' => 1,
            'validfrom' => gmdate('Y-m-d H:i:s', strtotime('-1 day')),
            'validuntil' => gmdate('Y-m-d H:i:s', strtotime('+1 day')),
        ]);
        $this->assertTrue($participant->canBeUsed(), 'The current time is inside the validity period, the participant should be able to enter the survey.');
    }

    public function testTokenCanBeUsedBeforeValidityPeriod()
    {
        $participant = $this->createParticipant([
            'token' => 'a-token-string',
            'completed' => 'N',
            'usesleft' => 1,
            'validfrom' => gmdate('Y-m-d H:i:s', strtotime('+1 day')),
        ]);
        $this->assertFalse($participant->canBeUsed(), 'The validity period has not started yet, the participant should not be able to enter the survey.');
    }

    public function testTokenCanBeUsedAfterValidityPeriod()
    {
        $participant = $this->createParticipant([
            'token' => 'a-token-string',
            'completed' => 'Y',
            'usesleft' => 0,
            'validuntil' => gmdate('Y-m-d H:i:s', strtotime('-1 day')),
        ], true);
        $this->assertFalse($participant->canBeUsed(), 'The validity period has ended, the participant should not be able to enter the survey, even if editing after completion is allowed.');
    }

    public function testTokenCanBeEmailedWithoutTokenAttribute()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['token' => '']));
        $this->assertFalse($participant->canBeEmailed(), 'There is no access code, the participant should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithEmailStatusOk()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes());
        $this->assertTrue($participant->canBeEmailed(), 'Email status is OK, the participant should be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithEmailStatusNotOk()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['emailstatus' => '']));
        $this->assertFalse($participant->canBeEmailed(), 'Email status is empty, the participant should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithEmailStatusOptOut()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['emailstatus' => 'OptOut']));
        $this->assertFalse($participant->canBeEmailed(), 'The participant opted out, the participant should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedNoEmailSet()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['email' => '']));
        $this->assertFalse($participant->canBeEmailed(), 'Email was not set, the participant should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithCompletedAttributeSetToYes()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['completed' => 'Y']));
        $this->assertFalse($participant->canBeEmailed(), 'Completed was set to Y, the participant should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithCompletedAttributeSetToYesAndAllowEditAfterCompletionSetToYes()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['completed' => 'Y', 'usesleft' => 0]), true);
        $this->assertFalse($participant->canBeEmailed(), 'Completed was set to Y, the participant should not be able to be emailed, even if editing after completion is allowed.');
    }

    public function testTokenCanBeEmailedWithCompletedAttributeUnset()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['completed' => '']));
        $this->assertTrue($participant->canBeEmailed(), 'The completed attribute was not set, the participant should be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithNoUsesLeft()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['usesleft' => 0]));
        $this->assertFalse($participant->canBeEmailed(), 'No uses left, the participant should not be able to be emailed.');
    }

    public function testTokenCanBeEmailedWithNoUsesLeftAndAllowEditAfterCompletionSetToYes()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes(['usesleft' => 0]), true);
        $this->assertTrue($participant->canBeEmailed(), 'Editing after completion is allowed, the uses left should not matter.');
    }

    public function testTokenCanBeEmailedAfterValidityPeriod()
    {
        $participant = $this->createParticipant($this->getEmailableAttributes([
            'validuntil' => gmdate('Y-m-d H:i:s', strtotime('-1 day')),
        ]));
        $this->assertFalse($participant->canBeEmailed(), 'The validity period has ended, the participant should not be able to be emailed.');
    }
}
