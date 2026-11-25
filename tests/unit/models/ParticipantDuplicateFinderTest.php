<?php

namespace ls\tests;

use Participant;
use ParticipantAttributeName;
use ls\tests\DummyController;
use Yii;

/**
 * Duplicate detection in the central participant database (#20678, #19645) :
 * when firstname, lastname or email are encrypted, duplicates are found with duplicatefinder,
 * a keyed hash of the lowercase plain values computed when the participant is saved.
 */
class ParticipantDuplicateFinderTest extends TestBaseClass
{
    /** @var int[] attribute_id of the core attributes added by this test */
    private static $addedAttributeIds = [];

    /** @var string[] participant_id of the participants created by a test */
    private $createdParticipantIds = [];

    /** @var mixed CPDB_duplicatefinder_bits before the test */
    private static $duplicatefinderBits;

    /**
     * Encrypt firstname, lastname and email in the central participant database.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        /* Adds encryptionduplicateindexkey if missing */
        Yii::app()->getComponent('sodium');
        self::$duplicatefinderBits = Yii::app()->getConfig('CPDB_duplicatefinder_bits');
        Yii::app()->setConfig('CPDB_duplicatefinder_bits', 128);

        foreach (['firstname', 'lastname', 'email'] as $coreAttribute) {
            $attributeName = ParticipantAttributeName::model()->findByAttributes(['defaultname' => $coreAttribute]);
            if ($attributeName) {
                /* Existing installation : keep it, encrypt it for the test */
                if ($attributeName->encrypted !== 'Y') {
                    self::markTestSkipped("Core attribute $coreAttribute exists and is not encrypted, the test does not change it.");
                }
                continue;
            }
            $attributeName = new ParticipantAttributeName();
            $attributeName->attribute_type = 'TB';
            $attributeName->defaultname = $coreAttribute;
            $attributeName->visible = 'TRUE';
            $attributeName->encrypted = 'Y';
            $attributeName->core_attribute = 'Y';
            $attributeName->save(false);
            self::$addedAttributeIds[] = (int) $attributeName->attribute_id;
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$addedAttributeIds)) {
            ParticipantAttributeName::model()->deleteByPk(self::$addedAttributeIds);
        }
        Yii::app()->setConfig('CPDB_duplicatefinder_bits', self::$duplicatefinderBits);
        parent::tearDownAfterClass();
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdParticipantIds)) {
            Participant::model()->deleteByPk($this->createdParticipantIds);
        }
        Yii::app()->setConfig('CPDB_duplicatefinder_bits', 128);
        parent::tearDown();
    }

    /**
     * Create a participant with encryptSave (computes duplicatefinder, encrypts the core attributes)
     * @param string[] $values
     * @param int $ownerUid
     * @return Participant
     */
    private function createParticipant(array $values, int $ownerUid = 1): Participant
    {
        $participant = new Participant();
        $participant->participant_id = Participant::genUuid();
        $participant->firstname = $values['firstname'];
        $participant->lastname = $values['lastname'];
        $participant->email = $values['email'];
        $participant->blacklisted = 'N';
        $participant->owner_uid = $ownerUid;
        $participant->created_by = 1;
        $this->assertTrue($participant->encryptSave(), json_encode($participant->getErrors()));
        $this->createdParticipantIds[] = $participant->participant_id;
        return $participant;
    }

    /** The core attributes are encrypted : duplicatefinder is used. */
    public function testCoreAttributesAreEncrypted(): void
    {
        $this->assertSame(3, (int) Participant::countCoreAttributeCrypted());
        $this->assertTrue(Participant::canUseDuplicateFinder());
    }

    /** duplicatefinder ignores the case, and its length depends on CPDB_duplicatefinder_bits. */
    public function testDuplicateFinderValueIgnoresTheCase(): void
    {
        $value = Participant::getDuplicateFinderValue(['firstname' => 'Sara', 'lastname' => 'Connor', 'email' => 'SARA@example.org']);

        $this->assertSame(32, strlen($value), '128 bits : 32 hexadecimal characters.');
        $this->assertSame(
            $value,
            Participant::getDuplicateFinderValue(['firstname' => 'sara', 'lastname' => 'CONNOR', 'email' => 'sara@example.org'])
        );
        $this->assertNotSame(
            $value,
            Participant::getDuplicateFinderValue(['firstname' => 'John', 'lastname' => 'Connor', 'email' => 'sara@example.org'])
        );
    }

    /** CPDB_duplicatefinder_bits set to 0 disables duplicatefinder. */
    public function testDuplicateFinderCanBeDisabled(): void
    {
        Yii::app()->setConfig('CPDB_duplicatefinder_bits', 0);

        $this->assertFalse(Participant::canUseDuplicateFinder());
        $this->assertSame('', Participant::getDuplicateFinderValue(['firstname' => 'Sara', 'lastname' => 'Connor', 'email' => '']));
    }

    /** CPDB_duplicatefinder_bits must be a multiple of 4 between 32 and 256. */
    public function testInvalidDuplicateFinderBitsThrows(): void
    {
        Yii::app()->setConfig('CPDB_duplicatefinder_bits', 30);

        $this->expectException(\CHttpException::class);
        Participant::canUseDuplicateFinder();
    }

    /** The participant is saved encrypted, with the duplicatefinder of its plain values. */
    public function testEncryptSaveComputesTheDuplicateFinder(): void
    {
        $values = ['firstname' => 'Sara', 'lastname' => 'Connor', 'email' => 'sara@example.org'];
        $participant = $this->createParticipant($values);

        $stored = Yii::app()->db->createCommand()
            ->select('firstname, duplicatefinder')
            ->from('{{participants}}')
            ->where('participant_id = :id', [':id' => $participant->participant_id])
            ->queryRow();
        $this->assertNotSame('Sara', $stored['firstname'], 'The firstname must be encrypted.');
        $this->assertSame(Participant::getDuplicateFinderValue($values), $stored['duplicatefinder']);
    }

    /** A duplicate is found with encrypted core attributes, whatever the case of the values. */
    public function testDuplicateIsFoundWithEncryptedAttributes(): void
    {
        $participant = $this->createParticipant(['firstname' => 'Sara', 'lastname' => 'Connor', 'email' => 'sara@example.org']);

        $duplicates = Participant::getDuplicates(['firstname' => 'sara', 'lastname' => 'connor', 'email' => 'SARA@example.org']);

        $this->assertCount(1, $duplicates);
        $this->assertSame($participant->participant_id, $duplicates[0]->participant_id);
        $this->assertSame('Sara', $duplicates[0]->firstname, 'Duplicates are returned decrypted.');
        $this->assertSame([], Participant::getDuplicates(['firstname' => 'John', 'lastname' => 'Connor', 'email' => 'sara@example.org']));
    }

    /** With an owner, only the participants of this owner are duplicates. */
    public function testDuplicatesAreFilteredByOwner(): void
    {
        $this->createParticipant(['firstname' => 'Kyle', 'lastname' => 'Reese', 'email' => 'kyle@example.org'], 1);
        $values = ['firstname' => 'Kyle', 'lastname' => 'Reese', 'email' => 'kyle@example.org'];

        $this->assertCount(1, Participant::getDuplicates($values, 1));
        $this->assertSame([], Participant::getDuplicates($values, 999999));
    }

    /** Without firstname, lastname and email, a participant is never a duplicate. */
    public function testEmptyParticipantIsNeverADuplicate(): void
    {
        $this->assertSame([], Participant::getDuplicates(['firstname' => '', 'lastname' => '', 'email' => '']));
    }

    /** A participant saved without duplicatefinder (before the update) is reported as outdated. */
    public function testOutdatedDuplicateFinderIsDetected(): void
    {
        $participant = $this->createParticipant(['firstname' => 'Miles', 'lastname' => 'Dyson', 'email' => 'miles@example.org']);
        Participant::model()->updateByPk($participant->participant_id, ['duplicatefinder' => '']);
        Yii::app()->cache->flush();

        $criteria = new \CDbCriteria();
        $criteria->compare('participant_id', $participant->participant_id);
        $this->assertSame(1, Participant::getDuplicateFinderInvalidCount($criteria));
        $this->assertFalse(Participant::isDuplicateFinderUpToDate());

        /* Saving again computes it */
        $participant = Participant::model()->findByPk($participant->participant_id);
        $participant->decrypt();
        $this->assertTrue($participant->encryptSave());
        $this->assertSame(0, Participant::getDuplicateFinderInvalidCount($criteria));
    }

    /**
     * @param string $participantId
     * @return array the stored row
     */
    private function getStoredParticipant(string $participantId): array
    {
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{participants}}')
            ->where('participant_id = :id', [':id' => $participantId])
            ->queryRow();
    }

    /**
     * Run a CPDB maintenance action as superadmin
     * @param string $action reencryptParticipantData or recalculateDuplicateFinder
     * @return void
     */
    private function runMaintenance(string $action): void
    {
        Yii::import('application.controllers.admin.ParticipantsAction', true);
        Yii::app()->setController(new DummyController('dummyid'));
        Yii::app()->session['loginID'] = 1;
        Yii::app()->user->setId(1);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        try {
            (new \ParticipantsAction('dummy'))->$action();
        } finally {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            Yii::app()->user->setState('currentReencryptLastParticipantId', null);
            Yii::app()->user->setState('currentReencryptMethod', null);
            Yii::app()->user->setState('currentDuplicateFinderInvalidParticipantId', null);
            Yii::app()->user->setState('currentDuplicateFinderBits', null);
        }
    }

    /** Re-encryption saves only the encrypted columns : a column changed meanwhile is kept. */
    public function testReencryptionKeepsAColumnChangedMeanwhile(): void
    {
        $created = $this->createParticipant(['firstname' => 'Sarah', 'lastname' => 'Connor', 'email' => 'sarah@example.org']);
        $participant = Participant::model()->findByPk($created->participant_id);
        Participant::model()->updateByPk($created->participant_id, ['language' => 'fr']);

        $participant->decrypt();
        $this->assertTrue($participant->encryptSave(false, $participant->getAllEncryptedAttributes(0, 'Participant')));

        $this->assertSame('fr', $this->getStoredParticipant($created->participant_id)['language']);
    }

    /** The duplicate index update saves only duplicatefinder : a column changed meanwhile is kept. */
    public function testDuplicateFinderUpdateKeepsAColumnChangedMeanwhile(): void
    {
        $created = $this->createParticipant(['firstname' => 'John', 'lastname' => 'Connor', 'email' => 'john@example.org']);
        Participant::model()->updateByPk($created->participant_id, ['duplicatefinder' => '']);
        $participant = Participant::model()->findByPk($created->participant_id);
        Participant::model()->updateByPk($created->participant_id, ['language' => 'fr']);

        $participant->decrypt();
        $this->assertTrue($participant->encryptSave(false, ['duplicatefinder']));

        $stored = $this->getStoredParticipant($created->participant_id);
        $this->assertSame('fr', $stored['language']);
        $this->assertSame(
            Participant::getDuplicateFinderValue(['firstname' => 'John', 'lastname' => 'Connor', 'email' => 'john@example.org']),
            $stored['duplicatefinder']
        );
    }

    /** The re-encryption action re-encrypts the core attributes with the current method. */
    public function testReencryptionActionUsesTheCurrentMethod(): void
    {
        $method = Yii::app()->getConfig('CPDB_encryption_method');
        Yii::app()->setConfig('CPDB_encryption_method', 'B');
        $created = $this->createParticipant(['firstname' => 'Kate', 'lastname' => 'Brewster', 'email' => 'kate@example.org']);
        $this->assertSame(\LSActiveRecord::encryptSingle('Kate', 'B'), $this->getStoredParticipant($created->participant_id)['firstname']);

        Yii::app()->setConfig('CPDB_encryption_method', 'H');
        try {
            $this->runMaintenance('reencryptParticipantData');
        } finally {
            Yii::app()->setConfig('CPDB_encryption_method', $method);
        }

        $stored = $this->getStoredParticipant($created->participant_id);
        $this->assertNotSame(\LSActiveRecord::encryptSingle('Kate', 'B'), $stored['firstname'], 'Re-encrypted with the hardened method.');
        $this->assertSame('Kate', \LSActiveRecord::decryptSingle($stored['firstname'], 'H'));
        $this->assertSame(
            Participant::getDuplicateFinderValue(['firstname' => 'Kate', 'lastname' => 'Brewster', 'email' => 'kate@example.org']),
            $stored['duplicatefinder']
        );
    }

    /** The duplicate index update action computes the missing duplicatefinder. */
    public function testDuplicateFinderActionComputesTheMissingIndex(): void
    {
        $created = $this->createParticipant(['firstname' => 'Marcus', 'lastname' => 'Wright', 'email' => 'marcus@example.org']);
        Participant::model()->updateByPk($created->participant_id, ['duplicatefinder' => '']);

        $this->runMaintenance('recalculateDuplicateFinder');

        $this->assertSame(
            Participant::getDuplicateFinderValue(['firstname' => 'Marcus', 'lastname' => 'Wright', 'email' => 'marcus@example.org']),
            $this->getStoredParticipant($created->participant_id)['duplicatefinder']
        );
    }
}
