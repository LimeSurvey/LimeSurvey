<?php

namespace LimeSurvey\Helpers\Update;

class Update_168 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{participants}}', 'created', 'datetime');
            addColumn('{{participants}}', 'modified', 'datetime');
            addColumn('{{participants}}', 'created_by', 'integer');
            $oDB->createCommand('update {{participants}} set created_by=owner_uid')->query();
            alterColumn('{{participants}}', 'created_by', "integer", false);
    }
}
