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
            "boolean NOT NULL DEFAULT FALSE"
        );
    }
}
