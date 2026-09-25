<?php

namespace LimeSurvey\Helpers\Update;

use LsDefaultDataSets;

class Update_719 extends DatabaseUpdateBase
{
    /**
     * Register the standalone Map question type and migrate map-enabled Short Text questions onto it.
     */
    public function up()
    {
        $existing = $this->db->createCommand()
            ->select('id')
            ->from('{{question_themes}}')
            ->where('name = :name', [':name' => 'map'])
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
            ['group' => 'Mask questions'],
            'name = :name',
            [':name' => 'map']
        );

        $mapQuestionIds = $this->db->createCommand()
            ->select('q.qid')
            ->from('{{questions}} q')
            ->join('{{question_attributes}} qa', 'qa.qid = q.qid')
            ->where("q.parent_qid = 0")
            ->andWhere("q.type = :type", [':type' => 'S'])
            ->andWhere("(q.question_theme_name IS NULL OR q.question_theme_name = '' OR q.question_theme_name = 'core' OR q.question_theme_name = 'shortfreetext')")
            ->andWhere("qa.attribute = :attribute", [':attribute' => 'location_mapservice'])
            ->andWhere("qa.value IN ('1', '100')")
            ->group('q.qid')
            ->queryColumn();

        if (empty($mapQuestionIds)) {
            return;
        }

        foreach ($mapQuestionIds as $qid) {
            $this->db->createCommand()->update(
                '{{questions}}',
                [
                    'type' => 'J',
                    'question_theme_name' => 'map',
                ],
                'qid = :qid',
                [':qid' => $qid]
            );
        }
    }
}
