<?php

namespace LimeSurvey\Helpers\Update;

class Update_435 extends DatabaseUpdateBase
{
    public function up()
    {
            // Check if default survey groups exists - at some point it was possible to delete it
            $defaultSurveyGroupExists = $this->db->createCommand()
            ->select('gsid')
            ->from("{{surveys_groups}}")
            ->where('gsid = 1')
            ->queryScalar();
        if ($defaultSurveyGroupExists == false) {
            // Add missing default template
            $date = date("Y-m-d H:i:s");
            $this->db->createCommand()->insert('{{surveys_groups}}', array(
                'gsid'        => 1,
                'name'        => 'default',
                'title'       => 'Default',
                'description' => 'Default survey group',
                'sortorder'   => '0',
                'owner_id'   => '1',
                'created'     => $date,
                'modified'    => $date,
                'created_by'  => '1'
            ));
        }
            $this->db->createCommand()->addColumn('{{surveys_groups}}', 'alwaysavailable', "boolean NULL");
            $this->db->createCommand()->update(
                '{{surveys_groups}}',
                array(
                    'alwaysavailable' => '0',
                )
            );
            $this->db->createCommand()->update(
                '{{surveys_groups}}',
                array(
                    'alwaysavailable' => '0',
                ),
                "gsid=1"
            );
    }
}
