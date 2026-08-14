<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_710 extends DatabaseUpdateBase
{
    /**
     * Repairs PostgreSQL databases already migrated to LS7 with stale sequences. Update_700
     * rebuilt the response/timing tables with a fresh serial "id" (sequence starting at 1)
     * and copied existing rows in with their original ids, which never advanced the sequence,
     * causing duplicate primary key violations on the next response (bug #20640).
     * fixPostgresSequence() advances every sequence in the database past its column MAX,
     * which also covers any other tables affected by the same rename.
     *
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        if ($this->db->getDriverName() !== 'pgsql') {
            return;
        }
        \fixPostgresSequence();
    }
}
