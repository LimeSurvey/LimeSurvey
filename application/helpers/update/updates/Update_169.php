<?php

namespace LimeSurvey\Helpers\Update;

class Update_169 extends DatabaseUpdateBase
{
    public function up()
    {
            // Add new column for question index options.
            addColumn('{{surveys}}', 'questionindex', 'integer NOT NULL DEFAULT 0');
            // Set values for existing surveys.
            $this->db->createCommand("update {{surveys}} set questionindex = 0 where allowjumps <> 'Y'")->query();
            $this->db->createCommand("update {{surveys}} set questionindex = 1 where allowjumps = 'Y'")->query();

            // Remove old column.
            dropColumn('{{surveys}}', 'allowjumps');
    }
}
