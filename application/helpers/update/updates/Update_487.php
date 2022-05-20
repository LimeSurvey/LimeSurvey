<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Som it if it is a copy of update 453 which has been skipped on some installations
 */
class Update_487 extends DatabaseUpdateBase
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
        // Adjust permissions for "Survey Participants" menu entry
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                'permission' => 'tokens',
                'permission_grade' => 'read'
            ],
            "name='participants'"
        );
    }
}
