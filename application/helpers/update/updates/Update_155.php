<?php

namespace LimeSurvey\Helpers\Update;

class Update_155 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{surveys}}', 'googleanalyticsstyle', "string(1)");
            addColumn('{{surveys}}', 'googleanalyticsapikey', "string(25)");
        try {
            setTransactionBookmark();
            $this->db->createCommand()->renameColumn('{{surveys}}', 'showXquestions', 'showxquestions');
        } catch (Exception $e) {
            rollBackToTransactionBookmark();
        }
    }
}
