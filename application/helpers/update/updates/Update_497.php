<?php

namespace LimeSurvey\Helpers\Update;

use LsDefaultDataSets;

class Update_497 extends DatabaseUpdateBase
{
    public function up()
    {
        $themesToUpdate = ['1', 'A', 'B', 'C', 'E', 'F', 'K', 'M', 'P', 'Q', ':', ';'];
        $baseQuestionThemeEntries = LsDefaultDataSets::getBaseQuestionThemeEntries();
        foreach ($baseQuestionThemeEntries as $baseQuestionThemeEntry) {
            if (!in_array($baseQuestionThemeEntry['question_type'], $themesToUpdate)) {
                continue;
            }
            $this->db->createCommand()->update(
                "{{question_themes}}",
                ['settings' => $baseQuestionThemeEntry['settings']],
                'name=:themename',
                [':themename' => $baseQuestionThemeEntry['name']]
            );
        }
    }
}
