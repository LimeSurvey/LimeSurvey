<?php

namespace ls\tests\unit\helpers\remotecontrol;

use ls\tests\DummyController;

class InviteParticipantsTest extends BaseTest
{
    /** @var \DummyMailer */
    private static $plugin;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Load plugin
        self::$plugin = self::loadTestPlugin('DummyMailer');

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
        // Assert emails for participants 1, 2, 4 and 5 were sent
        $this->assertParticipantResultIsOk($result, 1);
        $this->assertParticipantResultIsOk($result, 2);
        $this->assertParticipantResultIsOk($result, 4);
        $this->assertParticipantResultIsOk($result, 5);
        // Assert participant 3 was skipped, as it is not valid anymore
        $this->assertArrayNotHasKey(3, $result);
        $this->assertSame('0 left to send', $result['status']);

        //Test invitation status in database.
        foreach ([1, 2, 4, 5] as $tid) {
            $participant = \Token::model(self::$surveyId)->findByAttributes(array('tid' => $tid));
            $this->assertNotSame('N', $participant->sent, 'Sent field in database should be a date, not ' . $participant->sent);
        }

        $participant_3 = \Token::model(self::$surveyId)->findByAttributes(array('tid' => 3));
        $this->assertSame('N', $participant_3->sent, 'Sent field in database should be N not ' . $participant_3->sent);
    }

    /**
     * Participants that are not valid yet or not valid anymore must not use up the email batch,
     * so valid participants after them are still invited.
     */
    public function testInviteParticipantsSkipsParticipantsOutsideValidityPeriodInBatch()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $newParticipants = array(
            array(
                'firstname' => 'Not valid yet',
                'email' => 'notvalidyet@example.com',
                'validfrom' => gmdate('Y-m-d H:i:s', strtotime('+1 year')),
            ),
            array(
                'firstname' => 'Not valid anymore',
                'email' => 'notvalidanymore@example.com',
                'validuntil' => gmdate('Y-m-d H:i:s', strtotime('-1 day')),
            ),
            array(
                'firstname' => 'Valid',
                'email' => 'valid@example.com',
                'validfrom' => gmdate('Y-m-d H:i:s', strtotime('-1 day')),
                'validuntil' => gmdate('Y-m-d H:i:s', strtotime('+1 year')),
            ),
        );
        $participantsData = $this->handler->add_participants($sessionKey, self::$surveyId, $newParticipants);
        $notValidYetTid = (int)$participantsData[0]['tid'];
        $notValidAnymoreTid = (int)$participantsData[1]['tid'];
        $validTid = (int)$participantsData[2]['tid'];

        $maxEmails = \Yii::app()->getConfig('maxemails');
        \Yii::app()->setConfig('maxemails', 2);
        try {
            $result = $this->handler->invite_participants($sessionKey, self::$surveyId);
        } finally {
            \Yii::app()->setConfig('maxemails', $maxEmails);
        }

        $this->assertParticipantResultIsOk($result, $validTid);
        $this->assertArrayNotHasKey($notValidYetTid, $result);
        $this->assertArrayNotHasKey($notValidAnymoreTid, $result);
        $this->assertSame('0 left to send', $result['status']);

        $notValidYet = \Token::model(self::$surveyId)->findByPk($notValidYetTid);
        $this->assertSame('N', $notValidYet->sent);
        $notValidAnymore = \Token::model(self::$surveyId)->findByPk($notValidAnymoreTid);
        $this->assertSame('N', $notValidAnymore->sent);
    }

    public function testAddNewParticipantWithoutALanguageAndInviteIt()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $newParticpants = array(
            array(
                'firstname' => 'Participant 6',
                'email' => 'participant6@example.com',
            )
        );

        $participantsData = $this->handler->add_participants($sessionKey, self::$surveyId, $newParticpants);
        $tid = (int)$participantsData[0]['tid'];

        $result = $this->handler->invite_participants($sessionKey, self::$surveyId, null, true, true);

        $this->assertParticipantResultIsOk($result, $tid);
    }

    /**
     * Participants that are not valid anymore must not use up the email batch when sending reminders.
     */
    public function testRemindParticipantsSkipsParticipantsOutsideValidityPeriodInBatch()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $sent = gmdate('Y-m-d H:i', strtotime('-1 week'));
        $newParticipants = array(
            array(
                'firstname' => 'Not valid anymore',
                'email' => 'remindnotvalidanymore@example.com',
                'sent' => $sent,
                'validuntil' => gmdate('Y-m-d H:i:s', strtotime('-1 day')),
            ),
            array(
                'firstname' => 'Valid',
                'email' => 'remindvalid@example.com',
                'sent' => $sent,
            ),
        );
        $participantsData = $this->handler->add_participants($sessionKey, self::$surveyId, $newParticipants);
        $notValidAnymoreTid = (int)$participantsData[0]['tid'];
        $validTid = (int)$participantsData[1]['tid'];

        $maxEmails = \Yii::app()->getConfig('maxemails');
        \Yii::app()->setConfig('maxemails', 1);
        try {
            $result = $this->handler->remind_participants($sessionKey, self::$surveyId, null, null, [$notValidAnymoreTid, $validTid]);
        } finally {
            \Yii::app()->setConfig('maxemails', $maxEmails);
        }

        $this->assertParticipantResultIsOk($result, $validTid);
        $this->assertArrayNotHasKey($notValidAnymoreTid, $result);

        $notValidAnymore = \Token::model(self::$surveyId)->findByPk($notValidAnymoreTid);
        $this->assertSame('N', $notValidAnymore->remindersent);
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
