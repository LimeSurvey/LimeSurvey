<?php

namespace LimeSurvey\Helpers\Update;

class Update_603 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->changeAdminTheme();
    }

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
