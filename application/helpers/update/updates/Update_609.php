<?php

namespace LimeSurvey\Helpers\Update;

class Update_609 extends DatabaseUpdateBase
{

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->update(
            "{{surveys_groupsettings}}",
            [
                'gsid' => 0
            ],
            "template = :template",
            [':template' => 'ls6_surveytheme']
        );
    }
}
