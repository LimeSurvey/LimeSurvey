<?php

namespace LimeSurvey\Helpers\Update;

class Update_450 extends DatabaseUpdateBase
{
    public function up()
    {

            $this->db->createCommand()->addColumn('{{archived_table_settings}}', 'attributes', 'text NULL');
            $archivedTableSettings = \Yii::app()->db->createCommand("SELECT * FROM {{archived_table_settings}}")->queryAll();
        foreach ($archivedTableSettings as $archivedTableSetting) {
            if ($archivedTableSetting['tbl_type'] === 'token') {
                $this->db->createCommand()->update('{{archived_table_settings}}', ['attributes' => json_encode(['unknown'])], 'id = :id', ['id' => $archivedTableSetting['id']]);
            }
        }
            // When encryptionkeypair is empty, encryption was never used (user comes from LS3), so it's safe to skip this udpate.
        if (!empty(\Yii::app()->getConfig('encryptionkeypair'))) {
            updateEncryptedValues450($this->db);
        }
    }
}
