<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_626 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $aDefaultBoxesEntries = \LsDefaultDataSets::getBoxesData();

        // delete old standard entries first
        $old_entries = ['List surveys', 'Global settings', 'Manage survey administrators', 'Label sets', 'Themes', 'Plugins'];
        foreach ($old_entries as $entry) {
            $this->db->createCommand()
                ->delete('{{boxes}}', 'title=:title', [':title' => $entry]);
        }

        // Push custom entries to lower position
        $customBoxes = $this->db->createCommand()
                ->select("*")->from("{{boxes}}")->queryAll();

        if ($customBoxes) {
            foreach ($customBoxes as $oBox) {
                $this->db->createCommand()->update(
                    '{{boxes}}',
                    [ "position" => $oBox['position'] + count($aDefaultBoxesEntries)],
                    'id=:id',
                    [':id' => $oBox['id']]
                );
            }
        }

        // Add the new boxes
        foreach ($aDefaultBoxesEntries as $aDefaultBoxesEntry) {
            $this->db->createCommand()->insert(
                "{{boxes}}",
                $aDefaultBoxesEntry
            );
        }
    }
}
