<?php

namespace LimeSurvey\Helpers\Update;

class Update_260 extends DatabaseUpdateBase
{
    public function up()
    {
            \alterColumn('{{participant_attribute_names}}', 'defaultname', "string(255)", false);
            \alterColumn('{{participant_attribute_names_lang}}', 'attribute_name', "string(255)", false);
    }
}
