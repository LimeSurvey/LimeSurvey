<?php

namespace LimeSurvey\Helpers\Update;

class Update_409 extends DatabaseUpdateBase
{
    public function up()
    {

            $sEncrypted = 'N';
            $this->db->createCommand()->update(
                '{{participant_attribute_names}}',
                array('encrypted' => $sEncrypted),
                "core_attribute='Y'"
            );
    }
}
