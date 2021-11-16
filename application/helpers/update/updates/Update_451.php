<?php

namespace LimeSurvey\Helpers\Update;

class Update_451 extends DatabaseUpdateBase
{
    public function up()
    {

            // When encryptionkeypair is empty, encryption was never used (user comes from LS3), so it's safe to skip this udpate.
        if (!empty(\Yii::app()->getConfig('encryptionkeypair'))) {
            // update wrongly encrypted custom attribute values for cpdb participants
            $encryptedAttributes = $this->db->createCommand()
                ->select('attribute_id')
                ->from('{{participant_attribute_names}}')
                ->where('encrypted = :encrypted AND core_attribute <> :core_attribute', ['encrypted' => 'Y', 'core_attribute' => 'Y'])
                ->queryAll();
            $nrOfAttributes = count($encryptedAttributes);
            foreach ($encryptedAttributes as $encryptedAttribute) {
                $attributes = $this->db->createCommand()
                    ->select('*')
                    ->from('{{participant_attribute}}')
                    ->where('attribute_id = :attribute_id', ['attribute_id' => $encryptedAttribute['attribute_id']])
                    ->queryAll();
                foreach ($attributes as $attribute) {
                    $attributeValue = \LSActiveRecord::decryptSingle($attribute['value']);
                    // This extra decrypt loop is needed because of wrongly encrypted attributes.
                    // @see d1eb8afbc8eb010104f94e143173f7d8802c607d
                    for ($i = 1; $i < $nrOfAttributes; $i++) {
                        $attributeValue = \LSActiveRecord::decryptSingleOld($attributeValue);
                    }
                    $recryptedValue = \LSActiveRecord::encryptSingle($attributeValue);
                    $updateArray['value'] = $recryptedValue;
                    $this->db->createCommand()->update(
                        '{{participant_attribute}}',
                        $updateArray,
                        'participant_id = :participant_id AND attribute_id = :attribute_id',
                        ['participant_id' => $attribute['participant_id'], 'attribute_id' => $attribute['attribute_id']]
                    );
                }
            }
        }
    }
}
