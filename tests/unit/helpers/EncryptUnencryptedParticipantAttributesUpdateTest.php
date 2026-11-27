<?php

namespace ls\tests;

use LimeSurvey\Helpers\Update\Update_723;

/**
 * DB update 723 encrypts values of encrypted participant attributes that a CSV import with overwrite saved unencrypted
 */
class EncryptUnencryptedParticipantAttributesUpdateTest extends TestBaseClass
{
    /** @var int[] */
    private $attributeIds = [];

    /** @var string */
    private $participantId;

    protected function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.helpers.update.updatedb_helper', true);
        $this->participantId = \Participant::genUuid();
        \Yii::app()->db->createCommand()->insert('{{participants}}', [
            'participant_id' => $this->participantId,
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'language' => 'en',
            'blacklisted' => 'N',
            'owner_uid' => 1,
            'created_by' => 1,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function tearDown(): void
    {
        $db = \Yii::app()->db;
        $participant = [':id' => $this->participantId];
        $db->createCommand()->delete('{{participant_attribute}}', 'participant_id = :id', $participant);
        $db->createCommand()->delete('{{participants}}', 'participant_id = :id', $participant);
        foreach ($this->attributeIds as $attributeId) {
            $db->createCommand()->delete(
                '{{participant_attribute_names}}',
                'attribute_id = :id',
                [':id' => $attributeId]
            );
        }
        parent::tearDown();
    }

    /**
     * Plain text values of an encrypted attribute are encrypted, everything else is left unchanged
     *
     * @return void
     */
    public function testEncryptsOnlyPlainTextValuesOfEncryptedAttributes()
    {
        $encryptedValue = \LSActiveRecord::encryptSingle('ALREADY-ENCRYPTED');
        $looksLikeCiphertext = 'ABCDEFGHIJKLMNOPQRSTUVWX';
        $plainText = $this->addValue('Y', 'ORIGINAL-VALUE');
        $alreadyEncrypted = $this->addValue('Y', $encryptedValue);
        $ambiguous = $this->addValue('Y', $looksLikeCiphertext);
        $empty = $this->addValue('Y', '');
        $notEncrypted = $this->addValue('N', 'PLAIN-ON-PURPOSE');

        (new Update_723(\Yii::app()->db, []))->safeUp();

        $this->assertNotSame('ORIGINAL-VALUE', $this->getValue($plainText));
        $this->assertSame('ORIGINAL-VALUE', \LSActiveRecord::decryptSingle($this->getValue($plainText)));
        $this->assertSame($encryptedValue, $this->getValue($alreadyEncrypted));
        $this->assertSame($looksLikeCiphertext, $this->getValue($ambiguous));
        $this->assertSame('', $this->getValue($empty));
        $this->assertSame('PLAIN-ON-PURPOSE', $this->getValue($notEncrypted));

        $encryptedOnce = $this->getValue($plainText);
        (new Update_723(\Yii::app()->db, []))->safeUp();
        $this->assertSame($encryptedOnce, $this->getValue($plainText));
    }

    /**
     * @param string $encrypted 'Y' or 'N'
     * @param string $value
     * @return int The attribute ID
     */
    private function addValue(string $encrypted, string $value): int
    {
        $db = \Yii::app()->db;
        $db->createCommand()->insert('{{participant_attribute_names}}', [
            'attribute_type' => 'TB',
            'defaultname' => 'update723_' . count($this->attributeIds),
            'visible' => 'TRUE',
            'encrypted' => $encrypted,
            'core_attribute' => 'N',
        ]);
        $attributeId = (int) $db->getLastInsertID();
        $this->attributeIds[] = $attributeId;
        $db->createCommand()->insert('{{participant_attribute}}', [
            'participant_id' => $this->participantId,
            'attribute_id' => $attributeId,
            'value' => $value,
        ]);
        return $attributeId;
    }

    private function getValue(int $attributeId): string
    {
        return (string) \Yii::app()->db->createCommand()
            ->select('value')
            ->from('{{participant_attribute}}')
            ->where(
                'participant_id = :pid AND attribute_id = :aid',
                [':pid' => $this->participantId, ':aid' => $attributeId]
            )
            ->queryScalar();
    }
}
