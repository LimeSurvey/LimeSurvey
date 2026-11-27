<?php

namespace LimeSurvey\Helpers\Update;

use Yii;

class Update_723 extends DatabaseUpdateBase
{
    /** A secretbox ciphertext holds at least the 16 byte MAC, which is 24 base64 characters */
    private const MIN_CIPHERTEXT_LENGTH = 24;

    /**
     * Encrypts values of encrypted participant attributes that were stored unencrypted
     *
     * A CSV import into the central participant database with the overwrite option saved these values without
     * encryption (issue #20884), and decrypting them failed. Only values that cannot be a ciphertext are encrypted:
     * Values that look like one but cannot be decrypted may belong to another key, so they are only logged.
     *
     * @return void
     */
    #[\Override]
    public function up()
    {
        if (!Yii::app()->sodium->bLibraryExists) {
            return;
        }
        $attributeIds = $this->db->createCommand()
            ->select('attribute_id')
            ->from('{{participant_attribute_names}}')
            ->where("encrypted = 'Y' AND core_attribute <> 'Y'")
            ->queryColumn();
        foreach ($attributeIds as $attributeId) {
            $rows = $this->db->createCommand()
                ->select('participant_id, value')
                ->from('{{participant_attribute}}')
                ->where('attribute_id = :attribute_id', [':attribute_id' => $attributeId])
                ->queryAll();
            foreach ($rows as $row) {
                $value = (string) $row['value'];
                if ($value === '' || $value === 'null') {
                    continue;
                }
                if ($this->canBeCiphertext($value)) {
                    if (!$this->canBeDecrypted($value)) {
                        Yii::log(
                            sprintf(
                                'Participant attribute %s of participant %s cannot be decrypted, left unchanged',
                                $attributeId,
                                $row['participant_id']
                            ),
                            \CLogger::LEVEL_WARNING,
                            'application.update'
                        );
                    }
                    continue;
                }
                $this->db->createCommand()->update(
                    '{{participant_attribute}}',
                    ['value' => Yii::app()->sodium->encrypt($value)],
                    'participant_id = :participant_id AND attribute_id = :attribute_id AND value = :old_value',
                    [
                        ':participant_id' => $row['participant_id'],
                        ':attribute_id' => $attributeId,
                        ':old_value' => $row['value'],
                    ]
                );
            }
        }
    }

    /**
     * @param string $value
     * @return bool
     */
    private function canBeCiphertext(string $value): bool
    {
        return strlen($value) >= self::MIN_CIPHERTEXT_LENGTH
            && strlen($value) % 4 === 0
            && preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $value) === 1;
    }

    /**
     * Tries the current key and the key used before
     *
     * @param string $value
     * @return bool
     */
    private function canBeDecrypted(string $value): bool
    {
        foreach (['sodium', 'sodiumOld'] as $component) {
            try {
                Yii::app()->$component->decrypt($value);
                return true;
            } catch (\Throwable $e) {
                // Try the next key
            }
        }
        return false;
    }
}
