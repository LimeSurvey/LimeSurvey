<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_649 extends DatabaseUpdateBase
{
    /**
     * @throws CException If a database update operation fails.
     */
    public function up()
    {
        $surveysTable = \Yii::app()->db->schema->getTable('{{surveys}}');
        if (!isset($surveysTable->columns['showregisterpolicy'])) {
            $this->db->createCommand()->addColumn('{{surveys}}', 'showregisterpolicy', "string(1) NOT NULL DEFAULT 'I'");
        }
        if (!isset($surveysTable->columns['showtokenpolicy'])) {
            $this->db->createCommand()->addColumn('{{surveys}}', 'showtokenpolicy', "string(1) NOT NULL DEFAULT 'I'");
        }
        $surveysGroupsSettingsTable = \Yii::app()->db->schema->getTable('{{surveys_groupsettings}}');
        if (!isset($surveysGroupsSettingsTable->columns['showregisterpolicy'])) {
            $this->db->createCommand()->addColumn('{{surveys_groupsettings}}', 'showregisterpolicy', "string(1) NOT NULL DEFAULT 'I'");
            /* Set global survey settings to 0 Don't show */
            $this->db->createCommand()->update('{{surveys_groupsettings}}', ['showregisterpolicy' => 'N'], 'gsid=0');
        }
        if (!isset($surveysGroupsSettingsTable->columns['showtokenpolicy'])) {
            $this->db->createCommand()->addColumn('{{surveys_groupsettings}}', 'showtokenpolicy', "string(1) NOT NULL DEFAULT 'I'");
            /* Set global survey settings to 0 Don't show */
            $this->db->createCommand()->update('{{surveys_groupsettings}}', ['showtokenpolicy' => 'N'], 'gsid=0');
        }
    }
}
