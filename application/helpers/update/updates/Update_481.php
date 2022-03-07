<?php

namespace LimeSurvey\Helpers\Update;

class Update_481 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->addColumn(
            '{{failed_login_attempts}}',
            'is_frontend',
            "boolean NULL"
        );
        $this->db->createCommand()->update('{{failed_login_attempts}}', array('is_frontend' => 0));
        \alterColumn('{{failed_login_attempts}}', 'is_frontend', "boolean", false);
    }
}
