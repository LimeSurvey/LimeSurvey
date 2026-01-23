<?php

namespace LimeSurvey\Helpers\Update;

class Update_642 extends DatabaseUpdateBase
{
    /**
     * Add savequotaexit column to surveys and surveys_groupsettings tables
     */
    public function up()
    {
        // Add to surveys table if not exists
        $surveysTable = $this->db->schema->getTable('{{surveys}}');
        if (!isset($surveysTable->columns['savequotaexit'])) {
            $this->db->createCommand()->addColumn(
                '{{surveys}}',
                'savequotaexit',
                "string(1) NOT NULL DEFAULT 'N'"
            );
        }

        // Add to surveys_groupsettings table if not exists
        $surveysGroupsettingsTable = $this->db->schema->getTable('{{surveys_groupsettings}}');
        if (!isset($surveysGroupsettingsTable->columns['savequotaexit'])) {
            $this->db->createCommand()->addColumn(
                '{{surveys_groupsettings}}',
                'savequotaexit',
                "string(1) NOT NULL DEFAULT 'N'"
            );
        }
    }
}
