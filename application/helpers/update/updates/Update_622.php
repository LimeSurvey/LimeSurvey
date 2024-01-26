<?php

namespace LimeSurvey\Helpers\Update;

use CDbException;
use CException;

class Update_622 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up(): void
    {
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex('{{answers_idx}}', '{{answers}}', ['qid', 'code', 'scale_id'], false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
    }
}
