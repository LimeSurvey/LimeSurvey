<?php

namespace LimeSurvey\Helpers\Update;

class Update_417 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
        $this->db->createCommand()->delete('{{surveymenu_entries}}', 'name=:name', [':name' => 'reorder']);
    }
}
