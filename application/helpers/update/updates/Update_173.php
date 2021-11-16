<?php

namespace LimeSurvey\Helpers\Update;

class Update_173 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{participant_attribute_names}}', 'defaultname', "string(50) NOT NULL default ''");
            upgradeCPDBAttributeDefaultNames173();
    }
}