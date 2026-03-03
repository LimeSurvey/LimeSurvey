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
                    $optionsJson = $templateConfiguration['options'];
                    $oldOptions = json_decode($optionsJson);
                    if (isset($oldOptions->checkicon) && $oldOptions->checkicon !== 'inherit') {
                        $oldOptions->checkicon = 'EB7A';
                        $newOptionsJson = json_encode($oldOptions);
                        $this->db->createCommand()->update(
                            '{{template_configuration}}',
                            ['options' => $newOptionsJson],
                            'id = :id',
                            [':id' => $templateConfiguration['id']]
                        );
                    }
                }
            }
        }
    }
}
