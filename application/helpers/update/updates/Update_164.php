<?php

namespace LimeSurvey\Helpers\Update;

class Update_164 extends DatabaseUpdateBase
{
    public function up()
    {
            upgradeTokens148(); // this should have bee done in 148 - that's why it is named this way
            // fix survey tables for missing or incorrect token field
            upgradeSurveyTables164();
            $this->db->createCommand()->update('{{settings_global}}', array('stg_value' => 164), "stg_name='DBVersion'");
    }
}
