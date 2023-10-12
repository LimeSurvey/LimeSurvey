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

        if (!isset($surveymenuTable->columns['active'])) {
            $this->db->createCommand()->addColumn('{{users}}', 'active', 'BOOLEAN DEFAULT TRUE');
        }

        $this->db->createCommand()->update('{{users}}', array('active' => 0), "expires is not null");
    }
}
