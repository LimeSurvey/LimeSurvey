<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_707 extends DatabaseUpdateBase
{
    /**
     * Correct the title and description of the "arrays/yesnouncertain" question theme to
     * match the rendered answer order (Yes / Uncertain / No). Only updates rows whose
     * current value still matches the previous string, leaving admin customisations intact.
     *
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $this->db->createCommand()->update(
            '{{question_themes}}',
            ['title' => 'Array (Yes/Uncertain/No)'],
            'name = :name AND title = :oldTitle',
            [
                ':name' => 'arrays/yesnouncertain',
                ':oldTitle' => 'Array (Yes/No/Uncertain)',
            ]
        );
        $this->db->createCommand()->update(
            '{{question_themes}}',
            ['description' => 'Array (Yes/Uncertain/No) question type configuration'],
            'name = :name AND description = :oldDescription',
            [
                ':name' => 'arrays/yesnouncertain',
                ':oldDescription' => 'Array (Yes/No/Uncertain) question type configuration',
            ]
        );
    }
}
