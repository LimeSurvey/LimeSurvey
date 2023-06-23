<?php

namespace ls\tests;

class InviteParticipantsTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /**
         * Import survey with five participants. Participant 3 is invalid (validuntil in the past)
         */
        $filename = self::$surveysFolder . '/limesurvey_survey_InviteParticipantsTest.lsa';
        self::importSurvey($filename);
        \Yii::app()->setController(new DummyController('dummyid'));
    }

    public function testInviteParticipants()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->invite_participants($sessionKey, self::$surveyId);
        // Assert emails for participants 1 and 2 were sent
        $this->assertParticipantResultIsOk($result, 1);
        $this->assertParticipantResultIsOk($result, 2);
        // Assert email for participant 3 was not sent
        $this->assertParticipantResultIsNotOk($result, 3);
        // Assert email sending stopped on error
        $this->assertArrayNotHasKey(4, $result);
        $this->assertArrayNotHasKey(5, $result);
    }

    public function testInviteParticipantsSkippingErrors()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->invite_participants($sessionKey, self::$surveyId, null, true, true);
        // Assert emails for participants 1 and 2 were sent
        $this->assertParticipantResultIsOk($result, 1);
        $this->assertParticipantResultIsOk($result, 2);
        // Assert email for participant 3 was not sent
        $this->assertParticipantResultIsNotOk($result, 3);
        // Assert emails for participants 4 and 5 were sent
        $this->assertParticipantResultIsOk($result, 4);
        $this->assertParticipantResultIsOk($result, 5);
    }

    /**
     * Assert the results for a participant are OK.
     * @param array<mixed> $results The raw results from invite_participants or remind_participants
     * @param int $tid  The participant ID
     */
    private function assertParticipantResultIsOk($results, $tid)
    {
        $this->assertArrayHasKey($tid, $results);
        $participantResults = $results[$tid];
        $this->assertArrayHasKey("status", $participantResults);
        $isOk = $this->isParticipantResultOk($participantResults, $tid);
        $this->assertTrue($isOk);
    }

    /**
     * Assert the results for a participant are not OK.
     * @param array<mixed> $results The raw results from invite_participants or remind_participants
     * @param int $tid  The participant ID
     */
    private function assertParticipantResultIsNotOk($results, $tid)
    {
        $this->assertArrayHasKey($tid, $results);
        $participantResults = $results[$tid];
        $this->assertArrayHasKey("status", $participantResults);
        $isOk = $this->isParticipantResultOk($participantResults, $tid);
        $this->assertFalse($isOk);
    }

    /**
     * Returns true if the results for a participant are OK.
     * @param array<mixed> Specific participant result from invite_participants or remind_participants
     * @param int $tid  The participant ID
     * @return bool
     */
    private function isParticipantResultOk($results, $tid)
    {
        // For this test, the result is considered OK if it's actually OK (status = OK),
        // or if it failed because email function couldn't be instantiated, as we don't
        // have a simple way to mock the LimeMailer. We could use a plugin that returns
        // true on beforeTokenEmail, but it doesn't seem much better than this.
        $validError = 'Could not instantiate mail function.';
        return $results['status'] == 'OK' || (!empty($results['error']) && $results['error'] == $validError);
    }
}
