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
                        $optionsJson = $templateConfiguration['options'];
                        $oldOptions = json_decode($optionsJson);
                        if (is_object($oldOptions)) {
                            if (empty($oldOptions->animatebody)) {
                                $oldOptions->animatebody = 'off';
                            }
                            if (empty($oldOptions->fixnumauto)) {
                                $oldOptions->fixnumauto = 'enable';
                            }
                            $newOptionsJson = json_encode($oldOptions);
                            $this->templateConfigurationOptionsUpdate(
                                $templateConfiguration['id'],
                                $newOptionsJson
                            );
                        }
                    } elseif ($templateConfiguration['template_name'] == 'fruity') {
                        $optionsJson = $templateConfiguration['options'];
                        // fixnumauto is not guaranteed to exist in older version of fruity,
                        // so rather decode as array, not as object
                        $oldOptions = json_decode($optionsJson);
                        if (is_object($oldOptions)) {
                            if (empty($oldOptions->fixnumauto)) {
                                $oldOptions->fixnumauto = 'enable';
                            }
                            $newOptionsJson = json_encode($oldOptions);
                            $this->templateConfigurationOptionsUpdate(
                                $templateConfiguration['id'],
                                $newOptionsJson
                            );
                        }
                    } elseif ($templateConfiguration['template_name'] == 'bootswatch') {
                        $optionsJson = $templateConfiguration['options'];
                        $oldOptions = json_decode($optionsJson);
                        if (is_object($oldOptions)) {
                            if (empty($oldOptions->hideprivacyinfo)) {
                                $oldOptions->hideprivacyinfo = 'off';
                            }
                            if (empty($oldOptions->fixnumauto)) {
                                $oldOptions->fixnumauto = 'enable';
                            }
                            $newOptionsJson = json_encode($oldOptions);
                            $this->templateConfigurationOptionsUpdate(
                                $templateConfiguration['id'],
                                $newOptionsJson
                            );
                        }
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
            ->where([
                'in',
                'template_name',
                ['vanilla', 'fruity', 'bootswatch']
            ])
            ->andWhere(['NOT IN', 'options', 'inherit'])
            ->queryAll();
    }

    public function templateConfigurationOptionsUpdate($id, $options)
    {
        $this->db->createCommand()->update(
            '{{template_configuration}}',
            ['options' => $options],
            'id = :id',
            [':id' => $id]
        );
    }
}
