<?php

namespace LimeSurvey\Helpers\Update;

use LsDefaultDataSets;

class Update_719 extends DatabaseUpdateBase
{
    /**
     * Register the standalone Map question type and migrate map-enabled Short Text questions onto it.
     */
    #[\Override]
    public function up()
    {
        $existing = $this->db->createCommand()
            ->select('id')
            ->from('{{question_themes}}')
            ->where('name = :name AND question_type = :type', [':name' => 'map', ':type' => 'J'])
            ->queryScalar();

        if (empty($existing)) {
            $mapTheme = null;
            foreach (LsDefaultDataSets::getBaseQuestionThemeEntries() as $themeEntry) {
                if ($themeEntry['name'] === 'map') {
                    $mapTheme = $themeEntry;
                    break;
                }
            }
            if ($mapTheme !== null) {
                $this->db->createCommand()->insert('{{question_themes}}', $mapTheme);
            }
        }

        $this->db->createCommand()->update(
            '{{question_themes}}',
            ['group' => 'Mask questions', 'xml_path' => 'application/views/survey/questions/answer/map'],
            'name = :name AND question_type = :type',
            [':name' => 'map', ':type' => 'J']
        );

        $mapQuestionIds = $this->db->createCommand()
            ->select('q.qid')
            ->from('{{questions}} q')
            ->join('{{question_attributes}} qa', 'qa.qid = q.qid')
            ->where("q.parent_qid = 0")
            ->andWhere("q.type = :type", [':type' => 'S'])
            // Questions on a custom Short Text theme stay untouched to keep their theme views and settings.
            ->andWhere("q.question_theme_name IS NULL OR q.question_theme_name IN ('', 'core', 'shortfreetext', 'browserdetect')")
            ->andWhere("qa.attribute = :attribute", [':attribute' => 'location_mapservice'])
            ->andWhere("qa.value IN ('1', '100')")
            // Short Text rendered a textarea, not a map, when display_rows was set.
            ->andWhere("NOT EXISTS (SELECT 1 FROM {{question_attributes}} dr WHERE dr.qid = q.qid AND dr.attribute = 'display_rows' AND dr.value <> '')")
            ->group('q.qid')
            ->queryColumn();

        if (empty($mapQuestionIds)) {
            return;
        }

        $this->db->createCommand()->update(
            '{{questions}}',
            [
                'type' => 'J',
                'question_theme_name' => 'map',
            ],
            ['in', 'qid', array_map('intval', $mapQuestionIds)]
        );
    }
}
