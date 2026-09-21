<?php

namespace ls\tests;

class ParticipantShareTest extends TestBaseClass
{
    /**
     * Regression test for issue #20699: a user who already holds an editable
     * share of a participant must not be able to reshare that participant to
     * another account, or to itself again with different rights. Only the
     * participant's owner, a superadmin, or a user with the participant panel
     * update permission may manage (create/update) a share.
     */
    public function testSharerWithEditableShareCannotReshareParticipant()
    {
        $originalLoginId = \Yii::app()->user->getId();
        // Act as superadmin while setting up fixtures.
        \Yii::app()->user->setId(1);

        $owner = self::createUserWithPermissions([
            'users_name' => 'shareTestOwner20699',
            'full_name'  => 'Share Test Owner',
            'email'      => 'sharetestowner20699@example.org',
            'lang'       => 'auto',
            'password'   => 'testpassword123',
        ]);

        $sharee = self::createUserWithPermissions(
            [
                'users_name' => 'shareTestSharee20699',
                'full_name'  => 'Share Test Sharee',
                'email'      => 'sharetestsharee20699@example.org',
                'lang'       => 'auto',
                'password'   => 'testpassword123',
            ],
            [
                'participantpanel' => ['read' => 'on'],
            ]
        );

        $participant = new \Participant();
        $participant->participant_id = $participant->genUuid();
        $participant->blacklisted = 'N';
        $participant->owner_uid = $owner->uid;
        $participant->created_by = $owner->uid;
        $this->assertTrue($participant->save(), 'Saved participant');

        // The sharee already holds an EDITABLE share of the participant.
        $existingShare = new \ParticipantShare();
        $existingShare->participant_id = $participant->participant_id;
        $existingShare->share_uid = $sharee->uid;
        $existingShare->date_added = date('Y-m-d H:i:s');
        $existingShare->can_edit = 1;
        $this->assertTrue($existingShare->save(), 'Saved initial editable share');

        try {
            \Yii::app()->user->setId($sharee->uid);

            $this->assertTrue(
                \ParticipantShare::model()->canEditSharedParticipant($participant->participant_id),
                'Sanity check: the sharee does hold an editable share'
            );

            $this->assertFalse(
                \ParticipantShare::model()->isAllowedToManageShare($participant->participant_id),
                'A user with only an editable share must not be allowed to manage/reshare the participant'
            );

            // Owner, by contrast, must still be allowed to manage shares of their own participant.
            \Yii::app()->user->setId($owner->uid);

            $this->assertTrue(
                \ParticipantShare::model()->isAllowedToManageShare($participant->participant_id),
                'The participant owner must still be allowed to manage shares'
            );
        } finally {
            \Yii::app()->user->setId(1);

            // Cleanup (as superadmin).
            \ParticipantShare::model()->deleteAllByAttributes(['participant_id' => $participant->participant_id]);
            $participant->delete();
            \User::model()->deleteByPk($owner->uid);
            \User::model()->deleteByPk($sharee->uid);

            \Yii::app()->user->setId($originalLoginId);
        }
    }
}
