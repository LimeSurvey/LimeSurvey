<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_499 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->changeAdminTheme();
    }

    /**
     * @throws CException
     */
    public function changeAdminTheme()
    {
        $this->db->createCommand()
            ->update(
                '{{settings_global}}',
                ['stg_value' => 'Sea_Green'],
                "stg_name = :stg_name",
                [':stg_name' => 'admintheme']
            );
    }
}
