<?php

namespace ls\tests;


class ParticipantTest extends BaseModelTestCase
{
    protected $modelClassName = \Participant::class;

    /**
     * Only UUIDs are accepted as participant IDs.
     *
     * @return void
     */
    public function testIsValidParticipantId()
    {
        $this->assertTrue(\Participant::isValidParticipantId(\Participant::genUuid()));
        $this->assertTrue(\Participant::isValidParticipantId('0A1B2C3D-4E5F-6071-8293-A4B5C6D7E8F9'));
        foreach (['', '12345', 'a"b<c', '0a1b2c3d-4e5f-6071-8293-a4b5c6d7e8f9 ', "0a1b2c3d-4e5f-6071-8293-a4b5c6d7e8f9\n", null, 12345] as $participantId) {
            $this->assertFalse(\Participant::isValidParticipantId($participantId), 'Participant ID ' . var_export($participantId, true) . ' should be invalid');
        }
    }

    /**
     * New participants need a UUID as participant ID, existing ones with an older ID can still be saved.
     *
     * @return void
     */
    public function testParticipantIdFormatOnlyValidatedOnInsert()
    {
        $participant = new \Participant();
        $participant->participant_id = 'a"b<c';
        $this->assertFalse($participant->validate(['participant_id']));

        $participant->participant_id = \Participant::genUuid();
        $this->assertTrue($participant->validate(['participant_id']));

        $participant->setScenario('update');
        $participant->participant_id = 'legacy-id-12345';
        $this->assertTrue($participant->validate(['participant_id']));
    }

    /**
     * The CSV import only inserts participants with a UUID as participant ID.
     *
     * @return void
     */
    public function testInsertParticipantCsvOnlyInsertsValidParticipantId()
    {
        $data = [
            'participant_id' => 'a"b<c',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'email' => 'max.mustermann@example.com',
            'language' => 'en',
            'blacklisted' => 'N',
            'owner_uid' => 1,
        ];
        $this->assertFalse(\Participant::model()->insertParticipantCSV($data));
        $this->assertNull(\Participant::model()->findByPk($data['participant_id']));

        $data['participant_id'] = \Participant::genUuid();
        try {
            $this->assertTrue(\Participant::model()->insertParticipantCSV($data));
            $this->assertInstanceOf(\Participant::class, \Participant::model()->findByPk($data['participant_id']));
        } finally {
            \Participant::model()->deleteByPk($data['participant_id']);
        }
    }
}
