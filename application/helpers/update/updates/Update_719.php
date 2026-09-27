<?php

namespace LimeSurvey\Helpers\Update;

class Update_719 extends DatabaseUpdateBase
{
    /**
     * Drop the tables of the legacy admin tutorial feature, which has been removed.
     * The new editor stores its tutorial state in the user settings instead.
     *
     * @return void
     */
    #[\Override]
    public function up()
    {
        $tables = [
            '{{tutorials}}',
            '{{tutorial_entries}}',
            '{{tutorial_entry_relation}}',
            '{{map_tutorial_users}}',
        ];
        foreach ($tables as $table) {
            if ($this->db->schema->getTable($table, true)) {
                $this->db->createCommand()->dropTable($table);
            }
        }
    }
}
