<?php

namespace LimeSurvey\Helpers\Update;

use CDbExpression;
use LimeSurvey\PluginManager\PluginManager;

class Update_705 extends DatabaseUpdateBase
{
    public function up()
    {
        // Set all plugins except ReactEditor to priority += 1
        $this->db->createCommand()->update(
            '{{plugins}}',
            ['priority' => new CDbExpression('priority + 1')],
            '`name` NOT IN (:re, :lsp)',
            [
                ':re' => 'ReactEditor',
                ':lsp' => 'LimeSurveyProfessional',
            ]
        );

        // Set ReactEditor to priority = 0
        $this->db->createCommand()->update(
            '{{plugins}}',
            ['priority' => 0],
            'name = :name',
            [':name' => 'ReactEditor']
        );
    }
}
