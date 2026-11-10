<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_719 extends DatabaseUpdateBase
{
    /**
     * Add the 'automatichyphenation' option (introduced in fruity_twentythree config.xml)
     * to all existing fruity_twentythree template_configuration records that are missing it.
     *
     * @throws CException
     */
    #[\Override]
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

        foreach ($templateConfigurations as $templateConfiguration) {
            $options = json_decode((string)$templateConfiguration['options']);
            if (!is_object($options) || property_exists($options, 'automatichyphenation')) {
                continue;
            }
            $options->automatichyphenation = 'on';
            $this->db->createCommand()->update(
                '{{template_configuration}}',
                ['options' => json_encode($options)],
                'id = :id',
                [':id' => $templateConfiguration['id']]
            );
        }
    }
}
