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
        // Keep custom themes and their questions together while reserving "map" for type J.
        $conflicts = $this->db->createCommand()
            ->select('id, question_type')
            ->from('{{question_themes}}')
            ->where('name = :name AND question_type <> :type', [':name' => 'map', ':type' => 'J'])
            ->queryAll();
        foreach ($conflicts as $conflict) {
            $themeName = 'map_legacy_' . $conflict['id'];
            while (
                $this->db->createCommand()
                ->select('id')
                ->from('{{question_themes}}')
                ->where('name = :name', [':name' => $themeName])
                ->queryScalar()
            ) {
                $themeName .= '_';
            }
            $this->db->createCommand()->update(
                '{{question_themes}}',
                ['name' => $themeName],
                'id = :id',
                [':id' => $conflict['id']]
            );
            $this->db->createCommand()->update(
                '{{questions}}',
                ['question_theme_name' => $themeName],
                'question_theme_name = :name AND type = :type',
                [':name' => 'map', ':type' => $conflict['question_type']]
            );
        }

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
            ['group' => 'Mask questions'],
            'name = :name AND question_type = :type',
            [':name' => 'map', ':type' => 'J']
        );

        $mapQuestionIds = $this->db->createCommand()
            ->select('q.qid')
            ->from('{{questions}} q')
            ->join('{{question_attributes}} qa', 'qa.qid = q.qid')
            ->where("q.parent_qid = 0")
            ->andWhere("q.type = :type", [':type' => 'S'])
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
