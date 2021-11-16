<?php

namespace LimeSurvey\Helpers\Update;

class Update_323 extends DatabaseUpdateBase
{
    public function run()
    {
            dropPrimaryKey('labels', 'lid');
            $oDB->createCommand()->addColumn('{{labels}}', 'id', 'pk');
            $oDB->createCommand()->createIndex(
                '{{idx4_labels}}',
                '{{labels}}',
                ['lid', 'sortorder', 'language'],
                false
            );
    }
}