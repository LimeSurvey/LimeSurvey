<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

/**
 * This update adds two new indexes to the quota and quota_members table
 * to speed up quota usage. In certain situations, this can speed up the
 * quota usage by a factor of 100.
 * This fix will also be included in the update dbversion 496 for version 5.x.
 * That is why it may fail if the index already exists, but that is not a problem,
 * because we catch the exception.
 *
 * @package LimeSurvey\Helpers\Update
 */

class Update_605 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // In Postgres we can't keep using the transaction after a command fails, so we can't just use
        // a try/catch block. Instead, we need to use CREATE INDEX IF NOT EXISTS.
        if ($this->db->driverName == 'pgsql') {
            $this->db->createCommand("CREATE INDEX IF NOT EXISTS {{idx1_quota_id}} ON {{quota_languagesettings}} (quotals_quota_id)")->execute();
            $this->db->createCommand("CREATE INDEX IF NOT EXISTS {{idx2_quota_id}} ON {{quota_members}} (quota_id)")->execute();
            return;
        }

        // If we are not in Postgres, we can use a try/catch block and just ignore the exception
        try {
            $this->db->createCommand()->createIndex('{{idx1_quota_id}}', '{{quota_languagesettings}}', ['quotals_quota_id']);
        } catch (\Exception $e) {
            // Index already exists - ignore
        }
        try {
            $this->db->createCommand()->createIndex('{{idx2_quota_id}}', '{{quota_members}}', ['quota_id']);
        } catch (\Exception $e) {
            // Index already exists - ignore
        }
    }
}
