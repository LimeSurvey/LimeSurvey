<?php

namespace LimeSurvey\Helpers\Update;

class Update_472 extends DatabaseUpdateBase
{
    public function run()
    {

            $oDB->createCommand()->addColumn('{{users}}', 'last_forgot_email_password', 'datetime');
    }
}
