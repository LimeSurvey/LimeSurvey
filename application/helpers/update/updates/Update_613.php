<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_613 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $templateConfigurations = $this->db->createCommand()
            ->select('id, template_name, options')
            ->from('{{template_configuration}}')
            ->where(['in', 'template_name', ['vanilla', 'fruity', 'bootswatch']])
            ->andWhere(['NOT IN', 'options', 'inherit'])
            ->queryAll();

        if (!empty($templateConfigurations)) {
            foreach ($templateConfigurations as $templateConfiguration) {
                if ($templateConfiguration['options'] !== 'inherit') {
                    if ($templateConfiguration['template_name'] == 'vanilla') {
                        $sOptionsJson = $templateConfiguration['options'];
                        $oOldOptions = json_decode($sOptionsJson);
                        $oOldOptions->animatebody = 'off';
                        $oOldOptions->fixnumauto = 'enable';
                        $oNewOtionsJson = json_encode($oOldOptions);
                        $this->db->createCommand()->update(
                            '{{template_configuration}}',
                            ['options' => $oNewOtionsJson],
                            'id = :id',
                            [':id' => $templateConfiguration['id']]
                        );
                    } elseif ($templateConfiguration['template_name'] == 'fruity') {
                        $sOptionsJson = $templateConfiguration['options'];
                        $oOldOptions = json_decode($sOptionsJson);
                        $oOldOptions->fixnumauto = 'enable';
                        $oNewOtionsJson = json_encode($oOldOptions);
                        $this->db->createCommand()->update(
                            '{{template_configuration}}',
                            ['options' => $oNewOtionsJson],
                            'id = :id',
                            [':id' => $templateConfiguration['id']]
                        );
                    } elseif ($templateConfiguration['template_name'] == 'bootswatch') {
                        $sOptionsJson = $templateConfiguration['options'];
                        $oOldOptions = json_decode($sOptionsJson);
                        $oOldOptions->fixnumauto = 'enable';
                        $oOldOptions->hideprivacyinfo = 'off';
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
