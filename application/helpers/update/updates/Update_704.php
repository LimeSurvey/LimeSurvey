<?php

namespace LimeSurvey\Helpers\Update;

class Update_704 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->update('{{plugins}}', ['priority' => 1], ['name' => 'Authdb']);
    }
}
