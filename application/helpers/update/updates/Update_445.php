<?php

namespace LimeSurvey\Helpers\Update;

class Update_445 extends DatabaseUpdateBase
{
    public function up()
    {

            $table = '{{surveymenu_entries}}';
            $data_to_be_updated = [
                'data' => '{"render": {"isActive": false, "link": {"data": {"iSurveyID": ["survey","sid"]}}}}',
            ];
            $where = "name = 'activateSurvey'";
            $this->db->createCommand()->update(
                $table,
                $data_to_be_updated,
                $where
            );

            // Increase Database version
            $this->db->createCommand()->update(
                '{{settings_global}}',
                array('stg_value' => 445),
                "stg_name = 'DBVersion'"
            );
    }
}
