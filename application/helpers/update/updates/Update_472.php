<?php

namespace LimeSurvey\Helpers\Update;

class Update_472 extends DatabaseUpdateBase
{
    public function run()
    {

            $this->db->createCommand()->addColumn('{{users}}', 'last_forgot_email_password', 'datetime');
    }
}
