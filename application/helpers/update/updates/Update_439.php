<?php

namespace LimeSurvey\Helpers\Update;

class Update_439 extends DatabaseUpdateBase
{
    public function up()
    {

            // Some tables were renamed in dbversion 400 - their sequence needs to be fixed in Postgres
        if (\Yii::app()->db->driverName == 'pgsql') {
            fixPostgresSequence('questions');
            fixPostgresSequence('groups');
            fixPostgresSequence('answers');
            fixPostgresSequence('labels');
            fixPostgresSequence('defaultvalues');
        }
    }
}
