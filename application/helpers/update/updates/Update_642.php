<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_642 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $templateConfigurations = $this->db->createCommand()
            ->select('id, options')
            ->from('{{template_configuration}}')
            ->where('template_name = :template_name', [':template_name' => 'fruity_twentythree'])
            ->andWhere(['NOT IN', 'options', 'inherit'])
            ->queryAll();

        if (!empty($templateConfigurations)) {
            foreach ($templateConfigurations as $templateConfiguration) {
                if ($templateConfiguration['options'] !== 'inherit') {
                    $sOptionsJson = $templateConfiguration['options'];
                    $oOldOptions = json_decode($sOptionsJson);
                    if ($oOldOptions->checkicon !== 'inherit') {
                        $oOldOptions->checkicon = 'EB7A';
                        $oNewOtionsJson = json_encode($oOldOptions);
                        $this->db->createCommand()->update(
                            '{{template_configuration}}',
                            ['options' => $oNewOtionsJson],
                            'id = :id',
                            [':id' => $templateConfiguration['id']]
                        );
                    }
                }
            }
        }
    }
}
