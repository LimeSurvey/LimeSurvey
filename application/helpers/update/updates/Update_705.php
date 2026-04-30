<?php

namespace LimeSurvey\Helpers\Update;

use CDbExpression;
use LimeSurvey\PluginManager\PluginManager;

class Update_705 extends DatabaseUpdateBase
{
    public function up()
    {
        // Check if any plugin (excluding exceptions) has priority = 0
        $exists = $this->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{plugins}}')
            ->where('priority = 0 AND name NOT IN (:re, :eqh)', [
                ':re' => 'ReactEditor',
                ':eqh' => 'expressionQuestionHelp',
            ])
            ->queryScalar();

        if ($exists > 0) {
            // Increment all except ReactEditor and LimeSurveyProfessional
            // LimeSurveyProfessional was already incremented in Update_701
            $this->db->createCommand()->update(
                '{{plugins}}',
                ['priority' => new CDbExpression('priority + 1')],
                '`name` NOT IN (:re, :lsp)',
                [
                    ':re' => 'ReactEditor',
                    ':lsp' => 'LimeSurveyProfessional',
                ]
            );
        }

        // Set ReactEditor & expressionQuestionHelp to priority = 0
        $this->db->createCommand()->update(
            '{{plugins}}',
            ['priority' => 0],
            'name IN (:re, :eqh)',
            [
                ':re' => 'ReactEditor',
                ':eqh' => 'expressionQuestionHelp',
            ]
        );
    }
}
