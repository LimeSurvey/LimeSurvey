<?php

namespace LimeSurvey\Helpers\Update;

class Update_475 extends DatabaseUpdateBase
{
    public function up()
    {
        // Apply integrity fix before adding unique constraint.
        // List of label set ids which contain code duplicates.
        $lids = $this->db->createCommand(
            "SELECT {{labels}}.lid AS lid
            FROM {{labels}}
            GROUP BY {{labels}}.lid
            HAVING COUNT(DISTINCT({{labels}}.code)) < COUNT({{labels}}.id)"
        )->queryAll();
        foreach ($lids as $lid) {
            $hasLanguageColumn = false;
            regenerateLabelCodes400($lid['lid'], $hasLanguageColumn);
        }
        $this->db->createCommand()->createIndex('{{idx5_labels}}', '{{labels}}', ['lid','code'], true);
    }
}
