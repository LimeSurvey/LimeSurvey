<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_614 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $templateConfigurations = $this->getThemes();
        if (!empty($templateConfigurations)) {
            foreach ($templateConfigurations as $templateConfiguration) {
                if ($templateConfiguration['options'] !== 'inherit') {
                    if ($templateConfiguration['template_name'] == 'vanilla') {
                        $sOptionsJson = $templateConfiguration['options'];
                        $oOldOptions = json_decode($sOptionsJson);
                        if (empty($oOldOptions->animatebody)) {
                            $oOldOptions->animatebody = 'off';
                        }
                        if (empty($oOldOptions->fixnumauto)) {
                            $oOldOptions->fixnumauto = 'enable';
                        }
                        $oNewOtionsJson = json_encode($oOldOptions);
                        $this->db->createCommand()->update(
                            '{{template_configuration}}',
                            ['options' => $oNewOtionsJson],
                            'id = :id',
                            [':id' => $templateConfiguration['id']]
                        );
                    } elseif ($templateConfiguration['template_name'] == 'fruity') {
                        $sOptionsJson = $templateConfiguration['options'];
                        // fixnumauto is not guaranteed to exist in older version of fruity, so rather decode as array, not as object
                        $oldOptions = json_decode($sOptionsJson, true);
                        if (!isset($oldOptions['fixnumauto']) || empty($oldOptions['fixnumauto'])) {
                            $oldOptions['fixnumauto'] = 'enable';
                        }
                        $oNewOtionsJson = json_encode($oldOptions);
                        $this->db->createCommand()->update(
                            '{{template_configuration}}',
                            ['options' => $oNewOtionsJson],
                            'id = :id',
                            [':id' => $templateConfiguration['id']]
                        );
                    } elseif ($templateConfiguration['template_name'] == 'bootswatch') {
                        $sOptionsJson = $templateConfiguration['options'];
                        $oOldOptions = json_decode($sOptionsJson);
                        if (empty($oOldOptions->hideprivacyinfo)) {
                            $oOldOptions->hideprivacyinfo = 'off';
                        }
                        if (empty($oOldOptions->fixnumauto)) {
                            $oOldOptions->fixnumauto = 'enable';
                        }
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

    public function getThemes()
    {
        return $this->db->createCommand()
            ->select('id, template_name, options')
            ->from('{{template_configuration}}')
            ->where(['in', 'template_name', ['vanilla', 'fruity', 'bootswatch']])
            ->andWhere(['NOT IN', 'options', 'inherit'])
            ->queryAll();
    }
}
