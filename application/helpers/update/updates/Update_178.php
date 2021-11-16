<?php

namespace LimeSurvey\Helpers\Update;

class Update_178 extends DatabaseUpdateBase
{
    public function run()
    {
        if (Yii::app()->db->driverName == 'mysql') {
            modifyPrimaryKey('questions', array('qid', 'language'));
        }
    }
}
