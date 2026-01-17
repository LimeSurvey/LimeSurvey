<?php

namespace LimeSurvey\Helpers\Update;

class Update_641 extends DatabaseUpdateBase
{
    /**
     * Adding crypt_method coluln to
     * surveys and surveys_groupsettingsdefault as I
     * Update at surveys_groupsettings to B for gsid=0 (global) 
     *
     * @return void
     */
    public function up()
    {
        
        addColumn('{{surveys}}', 'crypt_method', "string(1) DEFAULT 'I'");
        addColumn('{{surveys_groupsettings}}', 'crypt_method', "string(1) DEFAULT 'I'");
        $this->db->createCommand()->update("{{surveys_groupsettings}}", ["crypt_method" => "B"], "gsid = 0");
    }
}
