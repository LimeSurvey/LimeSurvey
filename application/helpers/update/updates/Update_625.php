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
        //Restore all default menu entries before making them non-deletable.
        $aDefaultSurveyMenuEntries = \LsDefaultDataSets::getSurveyMenuEntryData();
        foreach ($aDefaultSurveyMenuEntries as $aSurveymenuentry) {
            if (\SurveymenuEntries::model()->findByAttributes(['name' => $aSurveymenuentry['name']]) === null) {
                $this->db->createCommand()->insert('{{surveymenu_entries}}', $aSurveymenuentry);
            }
        }

        //Adjust some menu titles
        $participantsEntry = \SurveymenuEntries::model()->find('name=:name', array(':name' => 'participants'));
        if ($participantsEntry) {
            $participantsEntry->menu_title = 'Participants';
            $participantsEntry->save();
        }

        $generalEntry = \SurveymenuEntries::model()->find('name=:name', array(':name' => 'generalsettings'));
        if ($generalEntry) {
            $generalEntry->menu_title = 'General';
            $generalEntry->save();
        }
    }
}
