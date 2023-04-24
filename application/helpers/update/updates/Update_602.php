<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_602 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->getAllBoxIcons();
    }

    /**
     * @throws CException
     */
    public function getAllBoxIcons()
    {
        $boxes = $this->db->createCommand()
            ->select('id,ico')
            ->from('{{boxes}}')
            ->queryAll();
        foreach ($boxes as $box) {
            if ($iconKey = array_search($box['ico'], array_column($oldIcons = $this->oldIcons(), 'icon'), false)) {
                $this->db->createCommand()
                    ->update(
                        '{{boxes}}',
                        ['ico' => (string)$oldIcons[$iconKey]['id']],
                        "ico = :icon",
                        [':icon' => $box['ico']]
                    );
            } elseif ($iconKey = array_search($box['ico'], array_column($newIcons = $this->newIcons(), 'icon'), false)) {
                $this->db->createCommand()
                    ->update(
                        '{{boxes}}',
                        ['ico' => (string)$newIcons[$iconKey]['id']],
                        "ico = :icon",
                        [':icon' => $box['ico']]
                    );
            } else {
                //replace by dummy icon if there is a unique icon that doesn't exist in the current iconlist
                $this->db->createCommand()
                    ->update(
                        '{{boxes}}',
                        ['ico' => '1'],
                        "ico = :icon",
                        [':icon' => $box['ico']]
                    );
            }
        }
    }

    /**
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function oldIcons(): array
    {
        return [
            ['id' => 1, 'icon' => 'icon-active'],
            ['id' => 2, 'icon' => 'icon-add'],
            ['id' => 3, 'icon' => 'icon-assessments'],
            ['id' => 4, 'icon' => 'icon-browse'],
            ['id' => 5, 'icon' => 'icon-conditions'],
            ['id' => 6, 'icon' => 'icon-copy'],
            ['id' => 7, 'icon' => 'icon-cpdb'],
            ['id' => 8, 'icon' => 'icon-databack'],
            ['id' => 9, 'icon' => 'icon-databegin'],
            ['id' => 10, 'icon' => 'icon-dataend'],
            ['id' => 11, 'icon' => 'icon-dataforward'],
            ['id' => 12, 'icon' => 'icon-defaultanswers'],
            ['id' => 13, 'icon' => 'icon-do'],
            ['id' => 14, 'icon' => 'icon-edit'],
            ['id' => 15, 'icon' => 'icon-emailtemplates'],
            ['id' => 10, 'icon' => 'icon-expired'],
            ['id' => 17, 'icon' => 'icon-export'],
            ['id' => 17, 'icon' => 'icon-exportcsv'],
            ['id' => 17, 'icon' => 'icon-exportr'],
            ['id' => 17, 'icon' => 'icon-exportspss'],
            ['id' => 17, 'icon' => 'icon-exportvv'],
            ['id' => 18, 'icon' => 'icon-expression'],
            ['id' => 19, 'icon' => 'icon-expressionmanagercheck'],
            ['id' => 20, 'icon' => 'icon-global'],
            ['id' => 21, 'icon' => 'icon-import'],
            ['id' => 21, 'icon' => 'icon-importcsv'],
            ['id' => 21, 'icon' => 'icon-importldap'],
            ['id' => 21, 'icon' => 'icon-importvv'],
            ['id' => 45, 'icon' => 'icon-inactive'],
            ['id' => 22, 'icon' => 'icon-invite'],
            ['id' => 23, 'icon' => 'icon-label'],
            ['id' => 23, 'icon' => 'icon-labels'],
            ['id' => 24, 'icon' => 'icon-list'],
            ['id' => 25, 'icon' => 'icon-logout'],
            ['id' => 26, 'icon' => 'icon-maximize'],
            ['id' => 27, 'icon' => 'icon-minimize'],
            ['id' => 28, 'icon' => 'icon-organize'],
            ['id' => 29, 'icon' => 'icon-quota'],
            ['id' => 30, 'icon' => 'icon-remind'],
            ['id' => 31, 'icon' => 'icon-renumber'],
            ['id' => 32, 'icon' => 'icon-resetsurveylogic'],
            ['id' => 33, 'icon' => 'icon-responses'],
            ['id' => 34, 'icon' => 'icon-saved'],
            ['id' => 35, 'icon' => 'icon-security'],
            ['id' => 13, 'icon' => 'icon-settings'],
            ['id' => 36, 'icon' => 'icon-shield'],
            ['id' => 37, 'icon' => 'icon-superadmin'],
            ['id' => 2, 'icon' => 'icon-survey'],
            ['id' => 38, 'icon' => 'icon-takeownership'],
            ['id' => 39, 'icon' => 'icon-template'],
            ['id' => 40, 'icon' => 'icon-templatepermissions'],
            ['id' => 39, 'icon' => 'icon-templates'],
            ['id' => 41, 'icon' => 'icon-tools'],
            ['id' => 42, 'icon' => 'icon-user'],
            ['id' => 43, 'icon' => 'icon-usergroup'],
            ['id' => 44, 'icon' => 'icon-viewlast'],
            ['id' => 46, 'icon' => 'fa fa-cart-plus'],
        ];
    }

    public function newIcons(): array
    {
        return [
            ['id' => 1, 'icon' => 'ri-play-fill'],
            ['id' => 2, 'icon' => 'ri-add-circle-fill'],
            ['id' => 3, 'icon' => 'ri-chat-3-line'],
            ['id' => 4, 'icon' => 'ri-chat-1-line'],
            ['id' => 5, 'icon' => 'ri-git-branch-fill'],
            ['id' => 6, 'icon' => 'ri-file-copy-line'],
            ['id' => 7, 'icon' => 'ri-shield-user-line'],
            ['id' => 8, 'icon' => 'ri-arrow-left-circle-fill'],
            ['id' => 9, 'icon' => 'ri-skip-back-fill'],
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
        ];
    }
}
