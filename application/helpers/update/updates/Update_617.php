<?php

namespace LimeSurvey\Helpers\Update;

use CDbException;
use CException;

class Update_617 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up(): void
    {
        $this->deleteDuplicateTemplateConfigurationEntries();
    }

    /**
     * @throws CDbException
     * @throws CException
     */
    private function deleteDuplicateTemplateConfigurationEntries(): void
    {
        $aKeepIds = $this->db->createCommand()
            ->select("MAX(id) AS maxrecordid")
            ->from("{{template_configuration}}")
            ->group(['template_name', 'sid', 'gsid', 'uid'])
            ->queryAll();
        $aKeepIds = array_column($aKeepIds, 'maxrecordid');
        $criteria = $this->db->getCommandBuilder()->createCriteria();
        $criteria->select = 'id, template_name, sid, gsid, uid';
        $criteria->addNotInCondition('id', $aKeepIds);
        $this->db->getCommandBuilder()->createDeleteCommand('{{template_configuration}}', $criteria)->execute();
    }
}
