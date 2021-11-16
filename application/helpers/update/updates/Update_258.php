<?php

namespace LimeSurvey\Helpers\Update;

class Update_258 extends DatabaseUpdateBase
{
    public function run()
    {
            Yii::app()->getDb()->createCommand(
                "DELETE FROM {{settings_global}} WHERE stg_name='adminimageurl'"
            )->execute();
    }
}