<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_166 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->renameTable('{{survey_permissions}}', '{{permissions}}');
        dropPrimaryKey('permissions');
        alterColumn('{{permissions}}', 'permission', "string(100)", false);
        $this->db->createCommand()->renameColumn('{{permissions}}', 'sid', 'entity_id');
        alterColumn('{{permissions}}', 'entity_id', "string(100)", false);
        addColumn('{{permissions}}', 'entity', "string(50)");
        $this->db->createCommand("update {{permissions}} set entity='survey'")->query();
        addColumn('{{permissions}}', 'id', 'pk');
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex(
                'idxPermissions',
                '{{permissions}}',
                'entity_id,entity,permission,uid',
                true
            );
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
        upgradePermissions166();
        dropColumn('{{users}}', 'create_survey');
        dropColumn('{{users}}', 'create_user');
        dropColumn('{{users}}', 'delete_user');
        dropColumn('{{users}}', 'superadmin');
        dropColumn('{{users}}', 'configurator');
        dropColumn('{{users}}', 'manage_template');
        dropColumn('{{users}}', 'manage_label');
        dropColumn('{{users}}', 'participant_panel');
        $this->db->createCommand()->dropTable('{{templates_rights}}');
    }
}
