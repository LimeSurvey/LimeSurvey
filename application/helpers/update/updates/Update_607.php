<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Some of it is a copy of update 453 which has been skipped on some installations
 */
class Update_607 extends DatabaseUpdateBase
{
    public function up()
    {
        $columnSchema = $this->db->getSchema()->getTable('{{archived_table_settings}}')->getColumn('attributes');
        if ($columnSchema === null) {
            $this->db->createCommand()->addColumn('{{archived_table_settings}}', 'attributes', 'text NULL');
            $archivedTableSettings = \Yii::app()->db->createCommand("SELECT * FROM {{archived_table_settings}}")->queryAll();
            foreach ($archivedTableSettings as $archivedTableSetting) {
                if ($archivedTableSetting['tbl_type'] === 'token') {
                    $this->db->createCommand()->update('{{archived_table_settings}}', ['attributes' => json_encode(['unknown'])], 'id = :id', ['id' => $archivedTableSetting['id']]);
                }
            }
        }
    }
}
