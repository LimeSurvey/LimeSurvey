<?php

namespace LimeSurvey\Helpers\Update;

class Update_355 extends DatabaseUpdateBase
{
    public function up()
    {

            $aIdMap = [];
            $aDefaultSurveyMenus = \LsDefaultDataSets::getSurveyMenuData();
        foreach ($aDefaultSurveyMenus as $i => $aSurveymenu) {
            $aIdMap[$aSurveymenu['name']] = $this->db->createCommand()
                ->select(['id'])
                ->from('{{surveymenu}}')
                ->where('name=:name', [':name' => $aSurveymenu['name']])
                ->queryScalar();
        }

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
    }
}
