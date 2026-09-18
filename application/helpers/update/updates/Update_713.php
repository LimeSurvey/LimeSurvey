<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_713 extends DatabaseUpdateBase
{
    /**
     * Adds the encryption_method column to surveys and surveys_groupsettings tables.
     * Sets the global default (gsid=0) to 'B' (Basic encryption method).
     *
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        /* Create or alter encryption_method column, handling cases where dev git users may already have it */
        $surveysTable = $this->db->schema->getTable('{{surveys}}', true);
        if (!isset($surveysTable->columns['encryption_method'])) {
            addColumn('{{surveys}}', 'encryption_method', "string(1) DEFAULT 'I'");
        } else {
            alterColumn('{{surveys}}', 'encryption_method', "string(1) DEFAULT 'I'");
        }

        $groupSettingsTable = $this->db->schema->getTable('{{surveys_groupsettings}}', true);
        if (!isset($groupSettingsTable->columns['encryption_method'])) {
            addColumn('{{surveys_groupsettings}}', 'encryption_method', "string(1) DEFAULT 'I'");
        } else {
            alterColumn('{{surveys_groupsettings}}', 'encryption_method', "string(1) DEFAULT 'I'");
        }
        /* Set global one to B (basic) if it's not hardened (only if I), didn't update any response table */
        $this->db->createCommand()->update("{{surveys_groupsettings}}", ["encryption_method" => "B"], "gsid = 0 AND (encryption_method IS NULL OR encryption_method <> 'H')");

        /* Create the duplicatefinder in participants for checking duplicate */
        $participantsTable = $this->db->schema->getTable('{{participants}}', true);
        if (!isset($participantsTable->columns['duplicatefinder'])) {
            addColumn('{{participants}}', 'duplicatefinder', "string(64) NOT NULL DEFAULT ''");
        } else {
            alterColumn('{{participants}}', 'duplicatefinder', "string(64) NOT NULL DEFAULT ''");
        }
        /* Add the index , do not break if it already exist */
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex('{{participants_duplicatefinder}}', '{{participants}}', ['duplicatefinder'], false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
    }
}
