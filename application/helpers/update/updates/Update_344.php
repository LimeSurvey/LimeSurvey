<?php

namespace LimeSurvey\Helpers\Update;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_344 extends DatabaseUpdateBase
{
    public function up()
    {

        // All templates should inherit from vanilla as default (if extends is empty).
        $this->db->createCommand()->update(
            '{{templates}}',
            [
                'extends' => 'vanilla',
            ],
            "extends = '' AND name != 'vanilla'"
        );

        // If vanilla template is missing, install it.
        $vanilla = $this->db
            ->createCommand()
            ->select('*')
            ->from('{{templates}}')
            ->where('name=:name', ['name' => 'vanilla'])
            ->queryRow();
        if (empty($vanilla)) {
            $vanillaData = [
                'name' => 'vanilla',
                'folder' => 'vanilla',
                'title' => 'Vanilla Theme',
                'creation_date' => date('Y-m-d H:i:s'),
                'author' => 'Louis Gac',
                'author_email' => 'louis.gac@limesurvey.org',
                'author_url' => 'https://www.limesurvey.org/',
                'copyright' => 'Copyright (C) 2007-2026 The LimeSurvey Project Team\\r\\nAll rights reserved.',
                'license' => 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
                'version' => '3.0',
                'api_version' => '3.0',
                'view_folder' => 'views',
                'files_folder' => 'files',
                'description' => '<strong>LimeSurvey Bootstrap Vanilla Survey Theme</strong><br>A clean and simple base that can be used by developers to create their own Bootstrap based theme.',
                'last_update' => null,
                'owner_id' => 1,
                'extends' => '',
            ];
            $this->db->createCommand()->insert('{{templates}}', $vanillaData);
        }
        $vanillaConf = $this->db
            ->createCommand()
            ->select('*')
            ->from('{{template_configuration}}')
            ->where('template_name=:template_name', ['template_name' => 'vanilla'])
            ->queryRow();
        if (empty($vanillaConf)) {
            $vanillaConfData = [
                'template_name' => 'vanilla',
                'sid' => null,
                'gsid' => null,
                'uid' => null,
                'files_css' => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
                'files_js' => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                'files_print_css' => '{"add":["css/print_theme.css"]}',
                'options' => '{"ajaxmode":"off","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png","font":"noto"}',
                'cssframework_name' => 'bootstrap',
                'cssframework_css' => '{}',
                'cssframework_js' => '',
                'packages_to_load' => '{"add":["pjax","font-noto"]}',
                'packages_ltr' => null,
                'packages_rtl' => null
            ];
            $this->db->createCommand()->insert('{{template_configuration}}', $vanillaConfData);
        }
    }
}
