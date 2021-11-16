<?php

namespace LimeSurvey\Helpers\Update;

class Update_354 extends DatabaseUpdateBase
{
    public function up()
    {
            $surveymenuTable = \Yii::app()->db->schema->getTable('{{surveymenu}}');

        if (!isset($surveymenuTable->columns['showincollapse'])) {
            $this->db->createCommand()->addColumn('{{surveymenu}}', 'showincollapse', 'integer DEFAULT 0');
        }

            $surveymenuEntryTable = \Yii::app()->db->schema->getTable('{{surveymenu}}');
        if (!isset($surveymenuEntryTable->columns['showincollapse'])) {
            $this->db->createCommand()->addColumn('{{surveymenu_entries}}', 'showincollapse', 'integer DEFAULT 0');
        }

            $aIdMap = [];
            $aDefaultSurveyMenus = \LsDefaultDataSets::getSurveyMenuData();
            switchMSSQLIdentityInsert('surveymenu', true);
        foreach ($aDefaultSurveyMenus as $i => $aSurveymenu) {
            $this->db->createCommand()->delete('{{surveymenu}}', 'name=:name', [':name' => $aSurveymenu['name']]);
            $this->db->createCommand()->delete('{{surveymenu}}', 'id=:id', [':id' => $aSurveymenu['id']]);
            $this->db->createCommand()->insert('{{surveymenu}}', $aSurveymenu);
            $aIdMap[$aSurveymenu['name']] = $aSurveymenu['id'];
        }
            switchMSSQLIdentityInsert('surveymenu', false);

            $aDefaultSurveyMenuEntries = \LsDefaultDataSets::getSurveyMenuEntryData();
        foreach ($aDefaultSurveyMenuEntries as $i => $aSurveymenuentry) {
            $this->db->createCommand()->delete(
                '{{surveymenu_entries}}',
                'name=:name',
                [':name' => $aSurveymenuentry['name']]
            );
            switch ($aSurveymenuentry['menu_id']) {
                case 1:
                    $aSurveymenuentry['menu_id'] = $aIdMap['settings'];
                    break;
                case 2:
                    $aSurveymenuentry['menu_id'] = $aIdMap['mainmenu'];
                    break;
                case 3:
                    $aSurveymenuentry['menu_id'] = $aIdMap['quickmenu'];
                    break;
                case 4:
                    $aSurveymenuentry['menu_id'] = $aIdMap['pluginmenu'];
                    break;
            }
            $this->db->createCommand()->insert('{{surveymenu_entries}}', $aSurveymenuentry);
        }
            unset($aDefaultSurveyMenuEntries);
    }
}
