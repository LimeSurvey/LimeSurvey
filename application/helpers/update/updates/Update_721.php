<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Add the missing removal of vanilla's noTablesOnMobile.css to the fruity configurations.
 * Fruity loads it itself, depending on its "Wrap tables" option, but LsDefaultDataSets did not store the
 * <remove> from its config.xml, so the file was always loaded and "Off" had no effect.
 */
class Update_721 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function up()
    {
        $templateConfigurations = $this->db->createCommand()
            ->select('id, files_css')
            ->from('{{template_configuration}}')
            ->where(
                'template_name = :template_name AND files_css <> :inherit',
                [':template_name' => 'fruity', ':inherit' => 'inherit']
            )
            ->queryAll();

        foreach ($templateConfigurations as $templateConfiguration) {
            $filesCss = json_decode((string) $templateConfiguration['files_css'], true);
            if (!is_array($filesCss)) {
                continue;
            }
            $filesToRemove = (array) ($filesCss['remove'] ?? []);
            if (in_array('css/noTablesOnMobile.css', $filesToRemove)) {
                continue;
            }
            $filesToRemove[] = 'css/noTablesOnMobile.css';
            $filesCss['remove'] = $filesToRemove;
            $this->db->createCommand()->update(
                '{{template_configuration}}',
                ['files_css' => json_encode($filesCss, JSON_UNESCAPED_SLASHES)],
                'id = :id',
                [':id' => $templateConfiguration['id']]
            );
        }
    }
}
