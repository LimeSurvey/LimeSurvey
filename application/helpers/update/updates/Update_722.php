<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_722 extends DatabaseUpdateBase
{
    /**
     * Adds the encryption_method column to surveys and surveys_groupsettings tables.
     * Sets the global default (gsid=0) to 'B' (Basic encryption method).
     *
     * @inheritDoc
     * @throws CException
     */
    #[\Override]
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
        $this->fillDuplicateFinder();
    }

    /**
     * Fill duplicatefinder of existing participants.
     * The value is a HMAC of plain firstname, lastname and email : it does not depend on encryption,
     * then it stays valid if encryption of these attributes is changed later.
     * Participants that can not be decrypted are left empty (can be fixed with the CPDB encryption maintenance).
     * @return void
     */
    private function fillDuplicateFinder()
    {
        /* Initialize sodium (adds encryptionduplicateindexkey if missing), then check if it can be used */
        try {
            if (!\Participant::canUseDuplicateFinder()) {
                return;
            }
        } catch (\Exception $e) {
            /* Invalid CPDB_duplicatefinder_bits : do not break the update, fixed later by maintenance */
            \Yii::log(
                "Update_722 : unable to fill participants duplicatefinder : " . $e->getMessage(),
                'error',
                'application.update'
            );
            return;
        }
        /* Core attributes encrypted : before this version, only Basic encryption */
        $encryptedCoreAttributes = $this->db->createCommand()
            ->select('defaultname')
            ->from('{{participant_attribute_names}}')
            ->where("core_attribute = 'Y' AND encrypted = 'Y'")
            ->queryColumn();
        $encryptedCoreAttributes = array_intersect($encryptedCoreAttributes, ['firstname', 'lastname', 'email']);
        /* Paginate with offset : the selected rows do not depend on the updated column,
         * and participant_id > :last can skip rows with old ids differing only by trailing spaces (MySQL PAD SPACE) */
        $batchSize = 1000;
        $offset = 0;
        $decryptErrors = 0;
        do {
            $participants = $this->db->createCommand()
                ->select('participant_id, firstname, lastname, email')
                ->from('{{participants}}')
                ->order('participant_id')
                ->limit($batchSize, $offset)
                ->queryAll();
            $offset += $batchSize;
            foreach ($participants as $participant) {
                try {
                    foreach ($encryptedCoreAttributes as $attribute) {
                        $participant[$attribute] = \LSActiveRecord::decryptSingle((string) $participant[$attribute], 'B');
                    }
                } catch (\Exception $e) {
                    /* Unable to decrypt (wrong key) : keep empty, fixed later by maintenance */
                    $decryptErrors++;
                    continue;
                }
                $this->db->createCommand()->update(
                    '{{participants}}',
                    ['duplicatefinder' => \Participant::getDuplicateFinderValue($participant)],
                    'participant_id = :participant_id',
                    [':participant_id' => $participant['participant_id']]
                );
            }
        } while (count($participants) === $batchSize);
        if ($decryptErrors > 0) {
            \Yii::log(
                sprintf("Update_722 : unable to decrypt %d participants, their duplicatefinder is not filled", $decryptErrors),
                'error',
                'application.update'
            );
        }
    }
}
