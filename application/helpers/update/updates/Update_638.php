<?php

namespace LimeSurvey\Helpers\Update;

class Update_638 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{surveys}}', 'crypt_method', "string(1) DEFAULT 'I'");
        addColumn('{{surveys_groupsettings}}', 'crypt_method', "string(1) DEFAULT 'I'");
        $this->db->createCommand()->update("{{surveys_groupsettings}}", ["crypt_method" => "B"], "gsid = 0");
    }
}
