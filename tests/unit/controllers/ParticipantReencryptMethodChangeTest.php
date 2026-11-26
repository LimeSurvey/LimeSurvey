<?php

namespace ls\tests\controllers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use Participant;
use ParticipantAttributeName;
use Yii;

/**
 * Re-encryption and duplicate index update of the central participant database in several runs (CPDB_reencrypt_limit) :
 * if CPDB_encryption_method or CPDB_duplicatefinder_bits changed since the first run, it stops and must be restarted (#19645).
 */
class ParticipantReencryptMethodChangeTest extends TestBaseClass
{
    /** @var string[] */
    private $createdParticipantIds = [];

    /** @var mixed */
    private $encryptionMethod;

    /** @var mixed */
    private $backupDuplicateFinderBits;

    protected function setUp(): void
    {
        parent::setUp();
        Yii::import('application.controllers.admin.ParticipantsAction', true);
        Yii::app()->setController(new DummyController('dummyid'));
        Yii::app()->session['loginID'] = 1;
        Yii::app()->user->setId(1);
        Yii::app()->session['aFlashMessage'] = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->encryptionMethod = Yii::app()->getConfig('CPDB_encryption_method');
        $this->backupDuplicateFinderBits = Yii::app()->getConfig('CPDB_duplicatefinder_bits');

        $participant = new Participant();
        $participant->participant_id = Participant::genUuid();
        $participant->firstname = 'Sara';
        $participant->blacklisted = 'N';
        $participant->owner_uid = 1;
        $participant->created_by = 1;
        $this->assertTrue($participant->encryptSave());
        $this->createdParticipantIds[] = $participant->participant_id;
    }

    protected function tearDown(): void
    {
        Participant::model()->deleteByPk($this->createdParticipantIds);
        Yii::app()->setConfig('CPDB_encryption_method', $this->encryptionMethod);
        Yii::app()->user->setState('currentReencryptLastParticipantId', null);
        Yii::app()->user->setState('currentReencryptMethod', null);
        Yii::app()->user->setState('currentReencryptFailed', null);
        Yii::app()->user->setState('currentDuplicateFinderInvalidParticipantId', null);
        Yii::app()->user->setState('currentDuplicateFinderBits', null);
        Yii::app()->setConfig('CPDB_duplicatefinder_bits', $this->backupDuplicateFinderBits);
        Yii::app()->session['aFlashMessage'] = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        parent::tearDown();
    }

    /** @return string[] the flash messages */
    private function getFlashMessages(): array
    {
        return array_column((array) Yii::app()->session['aFlashMessage'], 'message');
    }

    /** A run resumed with another method stops : the cursor is cleared, nothing is processed. */
    public function testResumedRunWithAnotherMethodStops(): void
    {
        Yii::app()->user->setState('currentReencryptLastParticipantId', '0');
        Yii::app()->user->setState('currentReencryptMethod', 'B');
        Yii::app()->setConfig('CPDB_encryption_method', 'H');

        (new \ParticipantsAction('dummy'))->reencryptParticipantData();

        $this->assertNull(Yii::app()->user->getState('currentReencryptLastParticipantId'));
        $this->assertNull(Yii::app()->user->getState('currentReencryptMethod'));
        $this->assertContains(
            'The encryption method changed during the re-encryption. Please restart the re-encryption from the first participant.',
            $this->getFlashMessages()
        );
        $this->assertNotContains('All participant data have been re-encrypted.', $this->getFlashMessages());
    }

    /** A complete run with the same method ends normally and clears its state. */
    public function testCompleteRunClearsTheState(): void
    {
        Yii::app()->setConfig('CPDB_encryption_method', 'H');

        (new \ParticipantsAction('dummy'))->reencryptParticipantData();

        $this->assertNull(Yii::app()->user->getState('currentReencryptLastParticipantId'));
        $this->assertNull(Yii::app()->user->getState('currentReencryptMethod'));
        $this->assertContains('All participant data have been re-encrypted.', $this->getFlashMessages());
    }

    /** Failures of a previous batch are reported at the end of the run, not "all re-encrypted". */
    public function testCompleteRunReportsFailuresOfPreviousBatches(): void
    {
        Yii::app()->user->setState('currentReencryptLastParticipantId', '0');
        Yii::app()->user->setState('currentReencryptMethod', 'H');
        Yii::app()->user->setState('currentReencryptFailed', 2);
        Yii::app()->setConfig('CPDB_encryption_method', 'H');

        (new \ParticipantsAction('dummy'))->reencryptParticipantData();

        $this->assertNull(Yii::app()->user->getState('currentReencryptFailed'));
        $this->assertNotContains('All participant data have been re-encrypted.', $this->getFlashMessages());
        $this->assertContains('2 participants could not be saved, see the error log.', $this->getFlashMessages());
    }

    /** An encrypted extra attribute is not a participants column : the core save must not use it. */
    public function testCompleteRunWithEncryptedExtraAttribute(): void
    {
        $attributeName = new ParticipantAttributeName();
        $attributeName->attribute_type = 'TB';
        $attributeName->defaultname = 'reencryptextra';
        $attributeName->visible = 'TRUE';
        $attributeName->encrypted = 'Y';
        $attributeName->core_attribute = 'N';
        $this->assertTrue($attributeName->save());
        try {
            Yii::app()->setConfig('CPDB_encryption_method', 'H');

            (new \ParticipantsAction('dummy'))->reencryptParticipantData();

            $this->assertContains('All participant data have been re-encrypted.', $this->getFlashMessages());
        } finally {
            $attributeName->delete();
        }
    }

    /** A duplicate index update resumed with another size stops : the cursor is cleared, nothing is processed. */
    public function testResumedDuplicateFinderRunWithAnotherSizeStops(): void
    {
        Yii::app()->user->setState('currentDuplicateFinderInvalidParticipantId', '0');
        Yii::app()->user->setState('currentDuplicateFinderBits', 64);
        Yii::app()->setConfig('CPDB_duplicatefinder_bits', 128);

        (new \ParticipantsAction('dummy'))->recalculateDuplicateFinder();

        $this->assertNull(Yii::app()->user->getState('currentDuplicateFinderInvalidParticipantId'));
        $this->assertNull(Yii::app()->user->getState('currentDuplicateFinderBits'));
        $this->assertContains(
            'The duplicate index size changed during the update. Please restart the update from the first participant.',
            $this->getFlashMessages()
        );
        $this->assertNotContains('All participant duplicate indexes are up to date.', $this->getFlashMessages());
    }

    /** A complete duplicate index update with the same size ends normally and clears its state. */
    public function testCompleteDuplicateFinderRunClearsTheState(): void
    {
        Participant::model()->updateByPk($this->createdParticipantIds, ['duplicatefinder' => '']);

        (new \ParticipantsAction('dummy'))->recalculateDuplicateFinder();

        $this->assertNull(Yii::app()->user->getState('currentDuplicateFinderInvalidParticipantId'));
        $this->assertNull(Yii::app()->user->getState('currentDuplicateFinderBits'));
        $this->assertContains('All participant duplicate indexes are up to date.', $this->getFlashMessages());
    }
}
