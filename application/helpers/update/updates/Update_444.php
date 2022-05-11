<?php

namespace LimeSurvey\Helpers\Update;

class Update_444 extends DatabaseUpdateBase
{
    public function up()
    {
            // Delete duplicate template configurations
            $deleteQuery = "DELETE FROM {{template_configuration}}
                WHERE id NOT IN (
                    SELECT id FROM (
                        SELECT MIN(id) as id
                            FROM {{template_configuration}} t 
                            GROUP BY t.template_name, t.sid, t.gsid, t.uid
                    ) x
                )";
            $this->db->createCommand($deleteQuery)->execute();
    }
}
