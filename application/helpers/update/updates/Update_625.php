<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_625 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $db = $this->db->createCommand();

        //Restore all default menu entries before making them non-deletable.
        $aDefaultSurveyMenuEntries = \LsDefaultDataSets::getSurveyMenuEntryData();
        foreach ($aDefaultSurveyMenuEntries as $aSurveymenuentry) {
            $menuEntryExist = $db->select("*")->from("{{surveymenu_entries}}")->where(
                'name=:name',
                [':name' => $aSurveymenuentry['name']]
            )->query()->rowCount;

            if (!$menuEntryExist) {
                $this->db->createCommand()->insert('{{surveymenu_entries}}', $aSurveymenuentry);
            }
        }

        //Adjust some menu titles
        $participantsEntry = $db->select("*")->from("{{surveymenu_entries}}")->where('name=:name', [':name' => 'participants'])->query()->rowCount;
        if ($participantsEntry) {
            $this->db->createCommand()->update(
                '{{surveymenu_entries}}',
                [ "menu_title" => "Participants"],
                'name=:name',
                [':name' => 'participants']
            );
        }

        $generalEntry = $db->select("*")->from("{{surveymenu_entries}}")->where('name=:name', [':name' => 'generalsettings'])->query()->rowCount;
        if ($generalEntry) {
            $this->db->createCommand()->update(
                '{{surveymenu_entries}}',
                ["menu_title" => "General"],
                'name=:name',
                [':name' => 'generalsettings']
            );
        }
    }
}
