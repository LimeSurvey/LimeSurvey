<?php

namespace LimeSurvey\Helpers\Update;

class Update_315 extends DatabaseUpdateBase
{
    public function up()
    {

            $this->db->createCommand()->update(
                '{{template_configuration}}',
                array('packages_to_load' => '["pjax"]'),
                "templates_name='default' OR templates_name='material'"
            );
    }
}
