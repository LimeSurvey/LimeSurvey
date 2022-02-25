<?php

namespace LimeSurvey\Helpers\Update;

class Update_408 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->update(
                '{{participant_attribute_names}}',
                array('encrypted' => 'Y'),
                "core_attribute='Y'"
            );
    }
}
