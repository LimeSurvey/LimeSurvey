<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_645 extends DatabaseUpdateBase
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
            ->where(
                'template_name = :template_name AND LOWER(options) LIKE :optionValue',
                [
                    ':template_name' => 'fruity_twentythree',
                    ':optionValue' => '%f00c%'
                ]
            )
            ->queryAll();

        if (!empty($templateConfigurations)) {
            foreach ($templateConfigurations as $templateConfiguration) {
                $optionsJson = $templateConfiguration['options'];
                $oldOptions = json_decode($optionsJson);
                if (isset($oldOptions->checkicon) && strtolower($oldOptions->checkicon) == 'f00c') {
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
