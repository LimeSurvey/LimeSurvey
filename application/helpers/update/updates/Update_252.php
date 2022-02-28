<?php

namespace LimeSurvey\Helpers\Update;

class Update_252 extends DatabaseUpdateBase
{
    public function up()
    {
            \Yii::app()->db->createCommand()->addColumn('{{questions}}', 'modulename', 'string');
            // Update DBVersion
    }
}
