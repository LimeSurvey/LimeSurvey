<?php

namespace LimeSurvey\Helpers\Update;

class Update_632 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{surveys}}', 'access_mode', "string(1) DEFAULT 'O'");
        $sids = [];
        foreach (dbGetTablesLike('%token%') as $table) {
            if (strpos($table, "old") === false) {
                $split = explode("_", $table);
                $sids[] = $split[count($split) - 1];
            }
        }
        if (count($sids)) {
            $this->db->createCommand()->update("{{surveys}}", ["access_mode" => "C"], "sid in (" . implode(",", $sids) . ")");
        }
    }
}
