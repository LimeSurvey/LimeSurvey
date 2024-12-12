<?php

namespace LimeSurvey\Helpers\Update;

class Update_627 extends DatabaseUpdateBase
{
    public function up(): void
    {
        $this->db->createCommand()->addColumn("{{surveys}}", "open_mode", "varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N'");
        $openmode = $this->db->createCommand()
            ->select("COLUMN_NAME")
            ->from("information_schema.columns")
            ->where("TABLE_SCHEMA = database() AND TABLE_NAME = {{surveys}} AND COLUMN_NAME = 'open_mode'");
        ;
        if (empty($openmode)) {
            $this->db->createCommand()->addColumn("{{surveys}}", "open_mode", "varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N'");
        }
    }
}
