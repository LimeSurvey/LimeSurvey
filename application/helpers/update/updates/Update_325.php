<?php

namespace LimeSurvey\Helpers\Update;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_325 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->dropTable('{{templates}}');
        $this->db->createCommand()->dropTable('{{template_configuration}}');

        // templates
        $this->db->createCommand()->createTable(
            '{{templates}}',
            array(
                'id' => "pk",
                'name' => "string(150) NOT NULL",
                'folder' => "string(45) NULL",
                'title' => "string(100) NOT NULL",
                'creation_date' => "datetime NULL",
                'author' => "string(150) NULL",
                'author_email' => "string(255) NULL",
                'author_url' => "string(255) NULL",
                'copyright' => "text ",
                'license' => "text ",
                'version' => "string(45) NULL",
                'api_version' => "string(45) NOT NULL",
                'view_folder' => "string(45) NOT NULL",
                'files_folder' => "string(45) NOT NULL",
                'description' => "text ",
                'last_update' => "datetime NULL",
                'owner_id' => "integer NULL",
                'extends' => "string(150)  NULL",
            )
        );

        $this->db->createCommand()->createIndex('{{idx1_templates}}', '{{templates}}', 'name', false);
        $this->db->createCommand()->createIndex('{{idx2_templates}}', '{{templates}}', 'title', false);
        $this->db->createCommand()->createIndex('{{idx3_templates}}', '{{templates}}', 'owner_id', false);
        $this->db->createCommand()->createIndex('{{idx4_templates}}', '{{templates}}', 'extends', false);

        $headerArray = [
            'name',
            'folder',
            'title',
            'creation_date',
            'author',
            'author_email',
            'author_url',
            'copyright',
            'license',
            'version',
            'api_version',
            'view_folder',
            'files_folder',
            'description',
            'last_update',
            'owner_id',
            'extends'
        ];
        $this->db->createCommand()->insert(
            "{{templates}}",
            array_combine(
                $headerArray,
                [
                    'default',
                    'default',
                    'Advanced Template',
                    date('Y-m-d H:i:s'),
                    'Louis Gac',
                    'louis.gac@gitit-tech.com',
                    'https://www.gitit-tech.com/',
                    'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.',
                    'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
                    '1.0',
                    '3.0',
                    'views',
                    'files',
                    "<strong>LimeSurvey Advanced Template</strong><br>A template with custom options to show what it's possible to do with the new engines. Each template provider will be able to offer its own option page (loaded from template)",
                    null,
                    1,
                    ''
                ]
            )
        );

        $this->db->createCommand()->insert(
            "{{templates}}",
            array_combine(
                $headerArray,
                [
                    'material',
                    'material',
                    'Material Template',
                    date('Y-m-d H:i:s'),
                    'Louis Gac',
                    'louis.gac@gitit-tech.com',
                    'https://www.gitit-tech.com/',
                    'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.',
                    'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
                    '1.0',
                    '3.0',
                    'views',
                    'files',
                    '<strong>LimeSurvey Advanced Template</strong><br> A template extending default, to show the inheritance concept. Notice the options, differing from Default.<br><small>uses FezVrasta\'s Material design theme for Bootstrap 3</small>',
                    null,
                    1,
                    'default'
                ]
            )
        );

        $this->db->createCommand()->insert(
            "{{templates}}",
            array_combine(
                $headerArray,
                [
                    'monochrome',
                    'monochrome',
                    'Monochrome Templates',
                    date('Y-m-d H:i:s'),
                    'Louis Gac',
                    'louis.gac@gitit-tech.com',
                    'https://www.gitit-tech.com/',
                    'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.',
                    'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
                    '1.0',
                    '3.0',
                    'views',
                    'files',
                    '<strong>LimeSurvey Monochrome Templates</strong><br>A template with monochrome colors for easy customization.',
                    null,
                    1,
                    ''
                ]
            )
        );


        // template_configuration
        $this->db->createCommand()->createTable(
            '{{template_configuration}}',
            array(
                'id' => "pk",
                'template_name' => "string(150)  NOT NULL",
                'sid' => "integer NULL",
                'gsid' => "integer NULL",
                'uid' => "integer NULL",
                'files_css' => "text",
                'files_js' => "text",
                'files_print_css' => "text",
                'options' => "text ",
                'cssframework_name' => "string(45) NULL",
                'cssframework_css' => "text",
                'cssframework_js' => "text",
                'packages_to_load' => "text",
                'packages_ltr' => "text",
                'packages_rtl' => "text",
            )
        );

        $this->db->createCommand()->createIndex(
            '{{idx1_template_configuration}}',
            '{{template_configuration}}',
            'template_name',
            false
        );
        $this->db->createCommand()->createIndex(
            '{{idx2_template_configuration}}',
            '{{template_configuration}}',
            'sid',
            false
        );
        $this->db->createCommand()->createIndex(
            '{{idx3_template_configuration}}',
            '{{template_configuration}}',
            'gsid',
            false
        );
        $this->db->createCommand()->createIndex(
            '{{idx4_template_configuration}}',
            '{{template_configuration}}',
            'uid',
            false
        );

        $headerArray = [
            'template_name',
            'sid',
            'gsid',
            'uid',
            'files_css',
            'files_js',
            'files_print_css',
            'options',
            'cssframework_name',
            'cssframework_css',
            'cssframework_js',
            'packages_to_load',
            'packages_ltr',
            'packages_rtl'
        ];
        $this->db->createCommand()->insert(
            "{{template_configuration}}",
            array_combine(
                $headerArray,
                [
                    'default',
                    null,
                    null,
                    null,
                    '{"add": ["css/animate.css","css/template.css"]}',
                    '{"add": ["scripts/template.js", "scripts/ajaxify.js"]}',
                    '{"add":"css/print_template.css"}',
                    '{"ajaxmode":"off","brandlogo":"on", "brandlogofile": "./files/logo.png", "boxcontainer":"on", "backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
                    'bootstrap',
                    '{"replace": [["css/bootstrap.css","css/flatly.css"]]}',
                    '',
                    '["pjax"]',
                    '',
                    ''
                ]
            )
        );

        $this->db->createCommand()->insert(
            "{{template_configuration}}",
            array_combine(
                $headerArray,
                [
                    'material',
                    null,
                    null,
                    null,
                    '{"add": ["css/bootstrap-material-design.css", "css/ripples.min.css", "css/template.css"]}',
                    '{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js", "scripts/ajaxify.js"]}',
                    '{"add":"css/print_template.css"}',
                    '{"ajaxmode":"off","brandlogo":"on", "brandlogofile": "./files/logo.png", "animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
                    'bootstrap',
                    '{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}',
                    '',
                    '["pjax"]',
                    '',
                    ''
                ]
            )
        );

        $this->db->createCommand()->insert(
            "{{template_configuration}}",
            array_combine(
                $headerArray,
                [
                    'monochrome',
                    null,
                    null,
                    null,
                    '{"add":["css/animate.css","css/ajaxify.css","css/sea_green.css", "css/template.css"]}',
                    '{"add":["scripts/template.js","scripts/ajaxify.js"]}',
                    '{"add":"css/print_template.css"}',
                    '{"ajaxmode":"off","brandlogo":"on","brandlogofile":".\/files\/logo.png","boxcontainer":"on","backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
                    'bootstrap',
                    '{}',
                    '',
                    '["pjax"]',
                    '',
                    ''
                ]
            )
        );
    }
}
