<?php

namespace LimeSurvey\Helpers\Update;

class Update_162 extends DatabaseUpdateBase
{
    public function up()
    {
            // Fix participant db types
            \alterColumn('{{participant_attribute}}', 'value', "text", false);
            \alterColumn('{{participant_attribute_names_lang}}', 'attribute_name', "string(255)", false);
            \alterColumn('{{participant_attribute_values}}', 'value', "text", false);
    }
}
