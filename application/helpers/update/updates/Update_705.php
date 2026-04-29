<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\PluginManager\PluginManager;

class Update_705 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->update('{{plugins}}', ['priority' => 1], 'name <> :name', [':name' => 'ReactEditor']);

        // Set ReactEditor to priority = 0
        $this->db->createCommand()->update('{{plugins}}', ['priority' => 0], 'name = :name', [':name' => 'ReactEditor']);
    }
}
