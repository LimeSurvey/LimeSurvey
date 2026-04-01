<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_646 extends DatabaseUpdateBase
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
                'template_name = :template_name AND options <> :optionValue',
                [
                    ':template_name' => 'fruity_twentythree',
                    ':optionValue' => 'inherit'
                ]
            )
            ->queryAll();

        if (!empty($templateConfigurations)) {
            foreach ($templateConfigurations as $templateConfiguration) {
                $optionsJson = $templateConfiguration['options'];
                $oldOptions = json_decode($optionsJson);
                if (empty($oldOptions->notables)) {
                    $oldOptions->notables = '1';
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
