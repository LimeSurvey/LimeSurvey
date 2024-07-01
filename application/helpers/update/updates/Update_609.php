<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_609 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $this->installFruityTwentyThree();
        // update global defaulttheme
        $this->db->createCommand()->update(
            "{{settings_global}}",
            ['stg_value' => 'fruity_twentythree'],
            "stg_name = :stg_name",
            [':stg_name' => 'defaulttheme']
        );
        // update current theme titles and description
        $this->db->createCommand()->update(
            "{{templates}}",
            [
                'title'       => 'Bootstrap Vanilla',
                'description' => gT("A clean and simple base that can be used by developers to create their own Bootstrap based theme.")
            ],
            "name = :name",
            [':name' => 'vanilla']
        );
        $this->db->createCommand()->update(
            "{{templates}}",
            [
                'title'       => 'Fruity',
                'description' => gT("A fruity theme for a flexible use. This theme offers monochromes variations and many options for easy customizations.")
            ],
            "name = :name",
            [':name' => 'fruity']
        );
        $this->db->createCommand()->update(
            "{{templates}}",
            [
                'title'       => 'Bootswatch',
                'description' => gT("Based on BootsWatch Themes:") . "<br><a href='https://bootswatch.com/3/'>" . gT("Visit Bootswatch page") . "</a>"
            ],
            "name = :name",
            [':name' => 'bootswatch']
        );
    }

    /**
     * @throws CException
     */
    private function installFruityTwentyThree()
    {
        // add fruity_twentythree theme
        $fruityTwentyThreeTheme = $this->db->createCommand()
            ->select('name')
            ->from('{{templates}}')
            ->where('name = :name', [':name' => 'fruity_twentythree'])
            ->queryRow();
        if (!$fruityTwentyThreeTheme) {
            $this->db->createCommand()->insert(
                "{{templates}}",
                $this->fruityTwentyThreeMetaData()
            );
        } else {
            $this->db->createCommand()->update(
                "{{templates}}",
                $this->fruityTwentyThreeMetaData(),
                "name = :name",
                [':name' => 'fruity_twentythree']
            );
        }
        // add fruity_twentythree configuration
        $fruityTwentyThreeConfigurations = $this->db->createCommand()
            ->select('template_name')
            ->from('{{template_configuration}}')
            ->where('template_name = :template_name', [':template_name' => 'fruity_twentythree'])
            ->queryAll();

        if (empty($fruityTwentyThreeConfigurations)) {
            $this->db->createCommand()->insert(
                "{{template_configuration}}",
                $this->fruityTwentyThreeInsertConfigData()
            );
        } else {
            foreach ($fruityTwentyThreeConfigurations as $fruityTwentyThreeConfiguration) {
                $this->db->createCommand()->update(
                    "{{template_configuration}}",
                    $this->fruityTwentyThreeUpdateConfigData(),
                    "template_name = :template_name",
                    [':template_name' => $fruityTwentyThreeConfiguration['template_name']]
                );
            }
        }
    }

    private function fruityTwentyThreeMetaData(): array
    {
        return [
            'name'          => 'fruity_twentythree',
            'folder'        => 'fruity_twentythree',
            'title'         => 'Fruity TwentyThree',
            'creation_date' => date('Y-m-d H:i:s'),
            'author'        => 'LimeSurvey GmbH',
            'author_email'  => 'info@limesurvey.org',
            'author_url'    => 'https://www.limesurvey.org/',
            'copyright'     => 'Copyright (C) 2005 - 2023 LimeSurvey Gmbh, Inc. All rights reserved.',
            'license'       => 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
            'version'       => '1.0.0',
            'api_version'   => '3.0',
            'view_folder'   => 'views',
            'files_folder'  => 'files',
            'description'   => gT("Our default theme for a fruity and flexible use. This theme offers single color variations"),
            'last_update'   => null,
            'owner_id'      => 1,
            'extends'       => '',
        ];
    }

    private function fruityTwentyThreeInsertConfigData(): array
    {
        return [
            'template_name'     => 'fruity_twentythree',
            'sid'               => null,
            'gsid'              => null,
            'uid'               => null,
            'files_css'         => '{"add":["css/variations/theme_apple.css","css/base.css","css/custom.css"], "remove":["survey.css", "template-core.css", "awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css", "awesome-bootstrap-checkbox/awesome-bootstrap-checkbox-rtl.css"]}',
            'files_js'          => '{"add":["scripts/theme.js","scripts/custom.js"], "remove":["survey.js", "template-core.js"]}',
            'files_print_css'   => '{"add":["css/print_theme.css"]}',
            'options'           => '{"hideprivacyinfo":"off","showpopups":"1","showclearall":"off","questionhelptextposition":"top","fixnumauto":"enable","backgroundimage":"off","backgroundimagefile":".\/files\/pattern.png",
                                     "brandlogo":"off","brandlogofile":"image::theme::files\/logo.png","font":"ibm-sans",
                                     "cssframework":{"@attributes":{"type":"dropdown","category":"Simple options",
                                     "width":"12","title":"Variations","parent":"cssframework"}}}',
            'cssframework_name' => '',
            'cssframework_css'  => '',
            'cssframework_js'   => '',
            'packages_to_load'  => '{"add":["pjax","moment","font-ibm-sans","font-ibm-serif"]}',
            'packages_ltr'      => null,
            'packages_rtl'      => null
        ];
    }

    private function fruityTwentyThreeUpdateConfigData(): array
    {
        return [
            'files_css'         => '{"add":["css/variations/theme_apple.css","css/base.css","css/custom.css"], "remove":["survey.css", "template-core.css", "awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css", "awesome-bootstrap-checkbox/awesome-bootstrap-checkbox-rtl.css"]}',
            'files_js'          => '{"add":["scripts/theme.js","scripts/custom.js"], "remove":["survey.js", "template-core.js"]}',
            'files_print_css'   => '{"add":["css/print_theme.css"]}',
            'options'           => '{"hideprivacyinfo":"off","showpopups":"1","showclearall":"off","questionhelptextposition":"top","fixnumauto":"enable","backgroundimage":"off","backgroundimagefile":".\/files\/pattern.png",
                                     "brandlogo":"off","brandlogofile":"image::theme::files\/logo.png","font":"ibm-sans",
                                     "cssframework":{"@attributes":{"type":"dropdown","category":"Simple options",
                                     "width":"12","title":"Variations","parent":"cssframework"}}}',
            'cssframework_name' => '',
            'cssframework_css'  => '',
            'cssframework_js'   => '',
            'packages_to_load'  => '{"add":["pjax","moment","font-ibm-sans","font-ibm-serif"]}',
            'packages_ltr'      => null,
            'packages_rtl'      => null
        ];
    }
}
