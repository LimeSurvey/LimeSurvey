<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_709 extends DatabaseUpdateBase
{
    /**
     * Adds the crypt_method column to surveys and surveys_groupsettings tables.
     * Sets the global default (gsid=0) to 'B' (Basic encryption method).
     *
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        /* Create or alter crypt_method column, handling cases where dev git users may already have it */
        if (!isset($surveysTable->columns['crypt_method'])) {
            addColumn('{{surveys}}', 'crypt_method', "string(1) DEFAULT 'I'");
        } else {
            alterColumn('{{surveys}}', 'crypt_method', "string(1) DEFAULT 'I'");
        }

        $groupSettingsTable = $this->db->schema->getTable('{{surveys_groupsettings}}', true);
        if (!isset($groupSettingsTable->columns['crypt_method'])) {
            addColumn('{{surveys_groupsettings}}', 'crypt_method', "string(1) DEFAULT 'I'");
        } else {
            alterColumn('{{surveys_groupsettings}}', 'crypt_method', "string(1) DEFAULT 'I'");
        }
        /* Set global one to B (basic), didn't update any response table */
        $this->db->createCommand()->update("{{surveys_groupsettings}}", ["crypt_method" => "B"], "gsid = 0");
    }
}
