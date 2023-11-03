<?php

namespace LimeSurvey\Helpers\Update;

class Update_616 extends DatabaseUpdateBase
{
    public function up()
    {
        switch ($this->db->driverName) {
            case 'mysql':
                \alterColumn(
                    '{{failed_emails}}',
                    'resend_vars',
                    'mediumtext',
                    false
                );
                break;
        }
    }
}
