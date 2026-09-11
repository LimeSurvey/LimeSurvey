<?php

namespace LimeSurvey\Helpers\Update;

class Update_707 extends DatabaseUpdateBase
{
    /**
     * disable all custom plugins for LS7 release
     */
    public function up()
    {
        $this->db->createCommand()
            ->update(
                '{{plugins}}',
                [
                    'active' => 0
                ],
                "plugin_type <> 'core'"
            );
    }
}
