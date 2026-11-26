<?php

namespace LimeSurvey\Helpers\Update;

class Update_723 extends DatabaseUpdateBase
{
    /**
     * Adds the code column (the internal title of a survey) to the surveys table, unless it already exists
     *
     * @return void
     */
    #[\Override]
    public function up()
    {
        $columnNames = $this->db->schema->getTable('{{surveys}}', true)->columnNames;

        if (!in_array('code', $columnNames, true)) {
            addColumn('{{surveys}}', 'code', 'string(128) NULL');
        }
    }
}
