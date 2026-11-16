<?php

namespace LimeSurvey\Helpers\Update;

class Update_173 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            addColumn('{{participant_attribute_names}}', 'defaultname', "string(50) NOT NULL default ''");
            upgradeCPDBAttributeDefaultNames173();
    }
}
