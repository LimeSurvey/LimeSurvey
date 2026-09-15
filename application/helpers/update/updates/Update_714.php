<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_714 extends DatabaseUpdateBase
{
    /**
     * Adds the duplicatefinder column for Participant
     * 
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        /* Create or alter encryption_method column, handling cases where users may already have it but update process stop */
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
