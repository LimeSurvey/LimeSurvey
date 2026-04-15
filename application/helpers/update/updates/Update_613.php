<?php

namespace LimeSurvey\Helpers\Update;

class Update_613 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $fixedIcons = [
            'overview' => 'ri-bar-chart-horizontal-line',
            'generalsettings' => 'ri-tools-line',
            'surveytexts' => 'ri-text-spacing',
            'datasecurity' => 'ri-shield-line',
            'theme_options' => 'ri-contrast-drop-fill',
            'presentation' => 'ri-slideshow-line',
            'tokens' => 'ri-body-scan-fill',
            'notification' => 'ri-notification-line',
            'publication' => 'ri-key-line',
            'surveypermissions' => 'ri-lock-password-line',
            'listQuestions' => '',
            'listQuestionGroups' => '',
            'reorder' => '',
            'participants' => '',
            'emailtemplates' => '',
            'failedemail' => '',
            'quotas' => '',
            'assessments' => '',
            'panelintegration' => '',
            'responses' => '',
            'statistics' => '',
            'resources' => '',
            'plugins' => '',
        ];

        foreach ($fixedIcons as $entryName => $newIcon) {
            $this->db->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    "menu_icon_type" => "remix",
                    "menu_icon" => $newIcon,
                ],
                'name=:name',
                [':name' => $entryName]
            );
        }
    }
}
