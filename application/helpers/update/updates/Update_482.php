<?php

namespace LimeSurvey\Helpers\Update;

class Update_482 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        \alterColumn('{{message}}', 'language', 'string(50)', false, '');
    }
}
