<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

/**
 * This update adds two new indexes to the quota and quota_members table
 * to speed up quota usage. In certain situations, this can speed up the
 * quota usage by a factor of 100.
 * This fix will also be included in the update dbversion 605 for version 6.x.
 *
 * @package LimeSurvey\Helpers\Update
 */
class Update_496 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->createIndex('{{idx1_quota_id}}', '{{quota_languagesettings}}', ['quotals_quota_id']);
        $this->db->createCommand()->createIndex('{{idx2_quota_id}}', '{{quota_members}}', ['quota_id']);
    }
}
