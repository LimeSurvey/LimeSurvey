<?php

namespace LimeSurvey\Helpers\Update;

class Update_482 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Allow label sets to contain full subquestion codes
        alterColumn('{{labels}}', 'code', "string(20)", false);
    }
}
