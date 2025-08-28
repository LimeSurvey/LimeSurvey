<?php

namespace LimeSurvey\Helpers\Update;

class Update_637 extends DatabaseUpdateBase
{
    public function up()
    {
        // Add new column to the surveys table to allow embedding of surveys
        addColumn('{{surveys}}', 'allow_embed', "string(1) DEFAULT 'N'");

        // Add new column to the surveys table to allow embedding of surveys
        $this->updateTemplateConfigurations();
    }

    public function updateTemplateConfigurations()
    {
        $templateConfigurations = $this->getThemes();
        if (!empty($templateConfigurations)) {
            foreach ($templateConfigurations as $templateConfiguration) {
                if ($templateConfiguration['options'] !== 'inherit') {
                    $optionsJson = $templateConfiguration['options'];
                    $oldOptions = json_decode($optionsJson);
                    if (is_object($oldOptions)) {
                        if (!isset($oldOptions->surveyembedding)) {
                            $oldOptions->surveyembedding = 'N';
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

    public function getThemes()
    {
        return $this->db->createCommand()
            ->select('id, template_name, options')
            ->from('{{template_configuration}}')
            ->where([
                'in',
                'template_name',
                ['vanilla', 'fruity', 'bootswatch', 'fruity_twentythree']
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
