<?php

namespace LimeSurvey\Helpers\Update;

class Update_150 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            addColumn('{{questions}}', 'relevance', 'text');
    }
}
