<?php

namespace LimeSurvey\Helpers\Update;

use CDbException;
use CException;

class Update_615 extends DatabaseUpdateBase
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
     */
    private function deleteDuplicateTemplateConfigurationEntries()
    {
        // count all entries
        $iTemplateConfigurationCount = $this->db->createCommand()
            ->select('count(*)')
            ->from('{{template_configuration}}')
            ->queryScalar();
        $limit = 1000;
        for ($i = 0; $i < $iTemplateConfigurationCount; $i += $limit) {
            $aTemplateConfigurations = $this->db->createCommand()
                ->select('template_name, sid, gsid, uid')
                ->from('{{template_configuration}}')
                ->limit($limit)
                ->queryAll();
            foreach ($aTemplateConfigurations as $aTemplateConfiguration) {
                // count duplicate entries of this entry ($aTemplateConfiguration)
                $iDuplicateConfigurationCommand = $this->db->createCommand()
                    ->select('count(*)')
                    ->from('{{template_configuration}}');
                $searchParams = [];
                if (is_null($aTemplateConfiguration['template_name'])) {
                    $templateNameSearchString = 'template_name IS NULL';
                    $iDuplicateConfigurationCommand->where($templateNameSearchString);
                } else {
                    $templateNameSearchString = 'template_name = :template_name';
                    $searchParams[':template_name'] = $aTemplateConfiguration['template_name'];
                    $iDuplicateConfigurationCommand->where($templateNameSearchString, [':template_name' => $aTemplateConfiguration['template_name']]);
                }
                if (is_null($aTemplateConfiguration['sid'])) {
                    $sidSearchString = 'sid IS NULL';
                    $iDuplicateConfigurationCommand->andWhere($sidSearchString);
                } else {
                    $sidSearchString = 'sid = :sid';
                    $searchParams[':sid'] = $aTemplateConfiguration['sid'];
                    $iDuplicateConfigurationCommand->andWhere($sidSearchString, [':sid' => $aTemplateConfiguration['sid']]);
                }
                if (is_null($aTemplateConfiguration['gsid'])) {
                    $gsidSearchString = 'gsid IS NULL';
                    $iDuplicateConfigurationCommand->andWhere($gsidSearchString);
                } else {
                    $gsidSearchString = 'gsid = :gsid';
                    $searchParams[':gsid'] = $aTemplateConfiguration['gsid'];
                    $iDuplicateConfigurationCommand->andWhere($gsidSearchString, [':gsid' => $aTemplateConfiguration['gsid']]);
                }
                if (is_null($aTemplateConfiguration['uid'])) {
                    $uidSearchString = 'uid IS NULL';
                    $iDuplicateConfigurationCommand->andWhere($uidSearchString);
                } else {
                    $uidSearchString = 'uid = :uid';
                    $searchParams[':uid'] = $aTemplateConfiguration['uid'];
                    $iDuplicateConfigurationCommand->andWhere($uidSearchString, [':uid' => $aTemplateConfiguration['uid']]);
                }
                $iDuplicateConfigurationCommand->params = $searchParams;
                $iDuplicateConfigurationCount = $iDuplicateConfigurationCommand->queryScalar();
                if ($iDuplicateConfigurationCount > 1) {
                    // delete all entries except 1
                    $iConfigurationsToDelete = $iDuplicateConfigurationCount - 1;
                    $oDeleteDuplicateCriteria = $this->db->getCommandBuilder()->createCriteria();
                    $oDeleteDuplicateCriteria->addCondition([$templateNameSearchString, $sidSearchString, $gsidSearchString, $uidSearchString]);
                    $oDeleteDuplicateCriteria->params = $searchParams;
                    $oDeleteDuplicateCriteria->limit = $iConfigurationsToDelete;
                    $this->db->getCommandBuilder()->createDeleteCommand('{{template_configuration}}', $oDeleteDuplicateCriteria)->execute();
                    // start from the beginning since we delete entries, until there are no more duplicates
                    $this->deleteDuplicateTemplateConfigurationEntries();
                    break 2;
                }
            }
        }
    }
}
