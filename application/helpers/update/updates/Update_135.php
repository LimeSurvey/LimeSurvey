<?php

namespace LimeSurvey\Helpers\Update;

class Update_135 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            \alterColumn('{{question_attributes}}', 'value', 'text');
    }
}
