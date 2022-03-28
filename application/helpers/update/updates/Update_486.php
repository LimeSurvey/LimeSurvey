<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Restore bootstrap_buttons name for Multiple Choice
 */
class Update_486 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->update("{{question_themes}}", ['name' => 'bootstrap_buttons'], "name='bootstrap_buttons_multi' and extends='M'");
    }
}
