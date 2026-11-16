<?php

namespace ls\tests;

/**
 * Tests for copying survey participants into the central participant database.
 */
class ParticipantCopyToCentralTest extends TestBaseClass
{
    /**
     * A survey participant with a participant ID that is not a UUID gets a new UUID, and this is reported.
     *
     * @return void
     */
    public function testInvalidParticipantIdIsReplacedAndReported()
    {
        \Yii::app()->session['loginID'] = 1;
        self::importSurvey(self::$surveysFolder . '/survey_archive_993688_participantBlacklist.lsa');
        \Yii::app()->db->createCommand()->update(
            '{{tokens_' . self::$surveyId . '}}',
            // Unique values, so no existing central participant counts as duplicate
            [
                'participant_id' => 'legacy-' . \Yii::app()->securityManager->generateRandomString(12),
                'email' => \Yii::app()->securityManager->generateRandomString(12) . '@example.com',
            ],
            'tid = 1'
        );

        \Yii::app()->session['participantid'] = '["1"]';
        $result = \Participant::model()->copyToCentral(self::$surveyId, [], []);

        $token = \TokenDynamic::model(self::$surveyId)->findByPk(1);
        try {
            $this->assertEquals(1, $result['success']);
            $this->assertEquals(1, $result['replacedparticipantid']);
            $this->assertTrue(\Participant::isValidParticipantId($token->participant_id));
            $this->assertInstanceOf(\Participant::class, \Participant::model()->findByPk($token->participant_id));
        } finally {
            \Participant::model()->deleteByPk($token->participant_id);
        }
    }
}
