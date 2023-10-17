<?php

namespace LimeSurvey\Helpers\Update;

class Update_499 extends DatabaseUpdateBase
{
    public function up()
    {
        switch ($this->db->driverName) {
            case 'mysql':
                \alterColumn(
                    '{{failed_emails}}',
                    'resend_vars',
                    'longtext',
                    false
                );
                break;
        }
    }
}
