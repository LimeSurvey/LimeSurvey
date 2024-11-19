<?php

namespace LimeSurvey\Helpers\Update;

class Update_493 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'menu_link' => 'surveyPermissions/index',
            ),
            "name='surveypermissions'"
        );
    }
}
