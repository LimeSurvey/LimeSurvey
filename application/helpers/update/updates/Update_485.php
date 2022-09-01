<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Add missing noTablesOnMobile.css to vanilla configs again. It was done on 428, but the bug on LsDefaultDataSets remained
 * causing problems on new installations.
 */
class Update_485 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Update vanilla config. This only applies to records with the previous "default" value (from LsDefaultDataSets).
        $this->db->createCommand()->update(
            '{{template_configuration}}',
            ['files_css' => '{"add":["css/base.css","css/theme.css","css/noTablesOnMobile.css","css/custom.css"]}'],
            "template_name = 'vanilla' AND files_css = :default_files_css",
            [':default_files_css' => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}']
        );
    }
}
