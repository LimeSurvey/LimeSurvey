<?php

namespace LimeSurvey\Helpers\Update;

class Update_600 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function up()
    {
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'menu_link' => 'quotas/index',
            ),
            "name='quotas'"
        );
    }
}
