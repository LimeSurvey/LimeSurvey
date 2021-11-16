<?php

namespace LimeSurvey\Helpers\Update;

class Update_168 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{participants}}', 'created', 'datetime');
            addColumn('{{participants}}', 'modified', 'datetime');
            addColumn('{{participants}}', 'created_by', 'integer');
            $this->db->createCommand('update {{participants}} set created_by=owner_uid')->query();
            \alterColumn('{{participants}}', 'created_by', "integer", false);
    }
}
