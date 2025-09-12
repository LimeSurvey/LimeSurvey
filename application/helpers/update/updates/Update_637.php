<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_637 extends DatabaseUpdateBase
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
                    $oOldOptions->cornerradius = '2';
                    $oOldOptions->bodybackgroundcolor = '#ffffff';
                    $oOldOptions->fontcolor = '#444444';
                    $oOldOptions->questionbackgroundcolor = '#ffffff';
                    $oOldOptions->checkicon = 'f00c';
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
