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
 * @
 */

class Update_605 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        try {
            $this->db->createCommand()->createIndex('{{idx1_quota_id}}', '{{quota_languagesettings}}', ['quotals_quota_id']);
        } catch (Exception $e) {
            // Index already exists - ignore
        }
        try {
            $this->db->createCommand()->createIndex('{{idx2_quota_id}}', '{{quota_members}}', ['quota_id']);
        } catch (Exception $e) {
            // Index already exists - ignore
        }
    }
}
