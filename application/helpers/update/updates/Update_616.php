<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_616 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $surveymenuTable = \Yii::app()->db->schema->getTable('{{users}}');

        if (!isset($surveymenuTable->columns['status'])) {
            $this->db->createCommand()->addColumn('{{users}}', 'status', 'BOOLEAN DEFAULT TRUE');
        }

        $this->db->createCommand()->update('{{users}}', array('status' => 0), "expires is not null");
    }
}
