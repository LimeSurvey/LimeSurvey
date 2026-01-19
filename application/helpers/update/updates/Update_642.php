<?php

namespace LimeSurvey\Helpers\Update;

class Update_642 extends DatabaseUpdateBase
{
    /**
     * Add savequotaexit column to surveys and surveys_groupsettings tables
     */
    public function up()
    {
        // Add to surveys table
        $this->db->createCommand()->addColumn(
            '{{surveys}}',
            'savequotaexit',
            "string(1) NOT NULL DEFAULT 'N'"
        );
        
        // Add to surveys_groupsettings table
        $this->db->createCommand()->addColumn(
            '{{surveys_groupsettings}}',
            'savequotaexit',
            "string(1) NOT NULL DEFAULT 'N'"
        );
    }
}
