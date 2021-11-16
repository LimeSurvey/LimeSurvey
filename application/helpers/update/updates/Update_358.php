<?php

namespace LimeSurvey\Helpers\Update;

class Update_358 extends DatabaseUpdateBase
{
    public function up()
    {
            dropColumn('{{sessions}}', 'data');
            addColumn('{{sessions}}', 'data', 'longbinary');
    }
}
