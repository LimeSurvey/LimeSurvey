<?php

namespace LimeSurvey\Helpers\Update;

class Update_713 extends DatabaseUpdateBase
{
    /**
     * Backfill plugin_type for rows left NULL on MSSQL installations that
     * ran the addColumn('plugin_type', ...) migration before it was fixed
     * to use uppercase DEFAULT/NULL keywords (see bug #19578). MSSQL only
     * applies a column's DEFAULT to pre-existing rows when the ADD COLUMN
     * statement includes WITH VALUES, so legacy user plugin rows were left
     * with plugin_type = NULL, causing an unguarded pluginDirs[] lookup to
     * fail in Plugin::getDir().
     *
     * The plugins table had no plugin_type column before Update_402, and
     * every plugin registered before that point was a user plugin (core
     * plugins are always inserted with an explicit plugin_type), so NULL
     * rows can be safely backfilled to 'user'.
     *
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->update(
            '{{plugins}}',
            ['plugin_type' => 'user'],
            'plugin_type IS NULL'
        );
    }
}
