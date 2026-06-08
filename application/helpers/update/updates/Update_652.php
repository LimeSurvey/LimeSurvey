<?php

namespace LimeSurvey\Helpers\Update;

class Update_652 extends DatabaseUpdateBase
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

        /* Drop already created columns */
        dropColumn('crypt_method', '{{surveys}}');
        dropColumn('crypt_method', '{{surveys_groupsettings}}');
        /* Create with good values */
        addColumn('{{surveys}}', 'crypt_method', "string(1) DEFAULT 'I'");
        addColumn('{{surveys_groupsettings}}', 'crypt_method', "string(1) DEFAULT 'I'");
        /* Set global one to B (basic), didn't update any response table */
        $this->db->createCommand()->update("{{surveys_groupsettings}}", ["crypt_method" => "B"], "gsid = 0");
    }
}
