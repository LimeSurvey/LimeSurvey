<?php

namespace LimeSurvey\Helpers\Update;

class Update_323 extends DatabaseUpdateBase
{
    public function run()
    {
            dropPrimaryKey('labels', 'lid');
            $this->db->createCommand()->addColumn('{{labels}}', 'id', 'pk');
            $this->db->createCommand()->createIndex(
                '{{idx4_labels}}',
                '{{labels}}',
                ['lid', 'sortorder', 'language'],
                false
            );
    }
}
