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
    public function up()
    {
        $this->deleteDuplicateTemplateConfigurationEntries();
    }

    /**
     * @throws CDbException
     * @throws CException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function deleteDuplicateTemplateConfigurationEntries()
    {
        $aKeepIds = $this->db->createCommand()
            ->select("MAX(id) AS maxRecordId")
            ->from("{{template_configuration}}")
            ->group(['template_name', 'sid', 'gsid', 'uid'])
            ->queryAll();
        $aKeepIds = array_column($aKeepIds, 'maxRecordId');
        $criteria = $this->db->getCommandBuilder()->createCriteria();
        $criteria->select = 'id, template_name, sid, gsid, uid';
        $criteria->addNotInCondition('id', $aKeepIds);
        $this->db->getCommandBuilder()->createDeleteCommand('{{template_configuration}}', $criteria)->execute();
    }
}
