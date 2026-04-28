<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Add labelsets->owner_id and index
 * @package LimeSurvey\Helpers\Update
 */
class Update_610 extends DatabaseUpdateBase
{
    public function up()
    {
        $table = $this->db->getSchema()->getTable('{{labelsets}}');
        if (!in_array('owner_id', $table->getColumnNames())) {
            $this->db->createCommand()->addColumn('{{labelsets}}', 'owner_id', "integer NULL");
            $this->db->createCommand()->createIndex('{{idx1_labelsets}}', '{{labelsets}}', 'owner_id', false);
            $this->db->createCommand()->createIndex('{{idx2_labelsets}}', '{{labelsets}}', ['lid','owner_id'], false);
        }
    }
}
