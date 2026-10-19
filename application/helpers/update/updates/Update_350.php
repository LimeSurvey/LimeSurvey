<?php

namespace LimeSurvey\Helpers\Update;

class Update_350 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            $this->db->createCommand()->createTable(
                '{{asset_version}}',
                array(
                    'id' => 'pk',
                    'path' => 'text NOT NULL',
                    'version' => 'integer NOT NULL',
                )
            );
    }
}
