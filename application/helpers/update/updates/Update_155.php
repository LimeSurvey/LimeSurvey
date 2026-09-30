<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_155 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
        addColumn('{{surveys}}', 'googleanalyticsstyle', "string(1)");
        addColumn('{{surveys}}', 'googleanalyticsapikey', "string(25)");
        try {
            setTransactionBookmark();
            $this->db->createCommand()->renameColumn('{{surveys}}', 'showXquestions', 'showxquestions');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
    }
}
