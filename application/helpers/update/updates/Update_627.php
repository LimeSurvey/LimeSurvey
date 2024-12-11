<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_627 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up(): void
    {
        $iconMappings = [
            ['id' => 1 , 'icon' => 'ri-play-fill'],
            ['id' => 2 , 'icon' => 'ri-add-circle-fill'],
            ['id' => 3 , 'icon' => 'ri-chat-3-line'],
            ['id' => 4 , 'icon' => 'ri-chat-1-line'],
            ['id' => 5 , 'icon' => 'ri-git-branch-fill'],
            ['id' => 6 , 'icon' => 'ri-file-copy-line'],
            ['id' => 7 , 'icon' => 'ri-shield-user-line'],
            ['id' => 8 , 'icon' => 'ri-arrow-left-circle-fill'],
            ['id' => 9 , 'icon' => 'ri-skip-back-fill'],
            ['id' => 10, 'icon' => 'ri-skip-forward-fill'],
            ['id' => 11, 'icon' => 'ri-arrow-right-circle-fill'],
            ['id' => 12, 'icon' => 'ri-grid-line'],
            ['id' => 13, 'icon' => 'ri-settings-5-fill'],
            ['id' => 14, 'icon' => 'ri-pencil-fill'],
            ['id' => 15, 'icon' => 'ri-mail-settings-line'],
            ['id' => 17, 'icon' => 'ri-download-fill'],
            ['id' => 18, 'icon' => 'ri-superscript'],
            ['id' => 19, 'icon' => 'ri-checkbox-fill'],
            ['id' => 20, 'icon' => 'ri-list-settings-line'],
            ['id' => 21, 'icon' => 'ri-upload-fill'],
            ['id' => 22, 'icon' => 'ri-mail-send-fill'],
            ['id' => 23, 'icon' => 'ri-price-tag-3-line'],
            ['id' => 24, 'icon' => 'ri-list-unordered'],
            ['id' => 25, 'icon' => 'ri-shut-down-line'],
            ['id' => 26, 'icon' => 'ri-fullscreen-fill'],
            ['id' => 27, 'icon' => 'ri-fullscreen-exit-fill'],
            ['id' => 28, 'icon' => 'ri-shape-fill'],
            ['id' => 29, 'icon' => 'ri-eject-fill'],
            ['id' => 30, 'icon' => 'ri-mail-volume-fill'],
            ['id' => 31, 'icon' => 'ri-list-ordered'],
            ['id' => 32, 'icon' => 'ri-survey-fill'],
            ['id' => 33, 'icon' => 'ri-exchange-funds-fill'],
            ['id' => 34, 'icon' => 'ri-save-line'],
            ['id' => 35, 'icon' => 'ri-lock-line'],
            ['id' => 36, 'icon' => 'ri-shield-check-fill'],
            ['id' => 37, 'icon' => 'ri-star-fill'],
            ['id' => 38, 'icon' => 'ri-user-shared-fill'],
            ['id' => 39, 'icon' => 'ri-brush-fill'],
            ['id' => 40, 'icon' => 'ri-admin-fill'],
            ['id' => 41, 'icon' => 'ri-tools-fill'],
            ['id' => 42, 'icon' => 'ri-user-fill'],
            ['id' => 43, 'icon' => 'ri-group-fill'],
            ['id' => 44, 'icon' => 'ri-history-line'],
            ['id' => 45, 'icon' => 'ri-stop-fill'],
            ['id' => 46, 'icon' => 'ri-shopping-cart-fill'],
            ['id' => 47, 'icon' => 'ri-user-line'],
            ['id' => 48, 'icon' => 'ri-settings-5-line'],
            ['id' => 49, 'icon' => 'ri-brush-line'],
            ['id' => 50, 'icon' => 'ri-add-line'],
            ['id' => 51, 'icon' => 'ri-function-fill'],
            ['id' => 52, 'icon' => 'ri-plug-line'],
            ['id' => 53, 'icon' => 'ri-user-settings-line'],
            ['id' => 54, 'icon' => 'ri-paint-fill'],
            ['id' => 55, 'icon' => 'ri-settings-3-fill'],
            ['id' => 56, 'icon' => 'ri-group-line'],
            ['id' => 57, 'icon' => 'ri-plug-fill'],
        ];

        foreach ($iconMappings as $iconMapping) {
            $this->db->createCommand()
            ->update(
                '{{boxes}}',
                ['ico' => $iconMapping['icon']],
                "ico = :icon",
                [':icon' => $iconMapping['id']]
            );
        }

        $urlIconMappings = [
            'dashboard/view' => 'ri-function-fill',
            'admin/globalsettings' => 'ri-settings-3-fill',
            'themeOptions' => 'ri-paint-fill',
            'userManagement/index' => 'ri-group-line',
            'admin/pluginmanager/sa/index' => 'ri-plug-fill',
        ];

        foreach ($urlIconMappings as $url => $icon) {
            $this->db->createCommand()
            ->update(
                '{{boxes}}',
                ['ico' => $icon],
                "url = :url",
                [':url' => $url]
            );
        }

        // Fix box order
        $boxes = $this->db->createCommand()->select("*")->from("{{boxes}}");
        $this->db->createCommand()->truncateTable('{{boxes}}');
        $this->db->createCommand()->insert('{{boxes}}', array(
            'id' => '1',
            'position' => '1',
            'url' => 'dashboard/view',
            'title' => 'Dashboard',
            'buttontext' => 'View dashboard',
            'ico' => 'ri-function-fill',
            'desc' => 'View dashboard',
            'page' => 'welcome',
            'usergroup' => '-1'
        ));
        $this->db->createCommand()->insert('{{boxes}}', array(
            'id' => '2',
            'position' => '2',
            'url' => 'admin/globalsettings',
            'title' => 'Global settings',
            'buttontext' => 'View global settings',
            'ico' => 'ri-settings-3-fill',
            'desc' => 'Edit global settings',
            'page' => 'welcome',
            'usergroup' => '-2'
        ));
        $this->db->createCommand()->insert('{{boxes}}', array(
            'id' => '3',
            'position' => '3',
            'url' => 'themeOptions',
            'title' => 'Themes',
            'buttontext' => 'Edit themes',
            'ico' => 'ri-paint-fill',
            'desc' => 'The themes functionality allows you to edit survey-, admin- or question themes.',
            'page' => 'welcome',
            'usergroup' => '-2'
        ));
        $this->db->createCommand()->insert('{{boxes}}', array(
            'id' => '4',
            'position' => '4',
            'url' => 'userManagement/index',
            'title' => 'Manage administrators',
            'buttontext' => 'Manage administrators',
            'ico' => 'ri-group-line',
            'desc' => 'The user management allows you to add additional users to your survey administration.',
            'page' => 'welcome',
            'usergroup' => '-2'
        ));
        $this->db->createCommand()->insert('{{boxes}}', array(
            'id' => '5',
            'position' => '5',
            'url' => 'admin/pluginmanager/sa/index',
            'title' => 'Plugins',
            'buttontext' => 'Manage plugins',
            'ico' => 'ri-plug-fill',
            'desc' => 'Plugins can be used to add custom features',
            'page' => 'welcome',
            'usergroup' => '-2'
        ));

        $existingUrls = array_keys($urlIconMappings);
        foreach ($boxes as $box) {
            if (in_array($box['url'], $existingUrls)) {
                $this->db->createCommand()->insert('{{boxes}}', $box);
            }
        }
    }
}
