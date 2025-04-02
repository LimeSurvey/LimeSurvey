<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_628 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        /* Delete old uneeded settings */
        $this->db->createCommand(
            "DELETE FROM {{settings_global}} WHERE " . $this->db->quoteColumnName('stg_name') . " LIKE 'last_survey_%'"
        )->execute();
        $this->db->createCommand(
            "DELETE FROM {{settings_global}} WHERE " . $this->db->quoteColumnName('stg_name') . " LIKE 'last_question_%'"
        )->execute();
    }
}
