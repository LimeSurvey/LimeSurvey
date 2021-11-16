<?php

namespace LimeSurvey\Helpers\Update;

class Update_417 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->delete('{{surveymenu_entries}}', 'name=:name', [':name' => 'reorder']);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 417), "stg_name='DBVersion'");
            $oTransaction->commit();
    }
}
