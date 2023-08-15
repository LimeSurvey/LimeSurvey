<?php

namespace LimeSurvey\Helpers\Update;

class Update_613 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-bar-chart-horizontal-line",
            ],
            'name=:name',
            [':name' => 'overview']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-tools-line",
            ],
            'name=:name',
            [':name' => 'generalsettings']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-text-spacing",
            ],
            'name=:name',
            [':name' => 'surveytexts']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-shield-line",
            ],
            'name=:name',
            [':name' => 'datasecurity']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-contrast-drop-fill",
            ],
            'name=:name',
            [':name' => 'theme_options']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-slideshow-line",
            ],
            'name=:name',
            [':name' => 'presentation']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-body-scan-fill",
            ],
            'name=:name',
            [':name' => 'tokens']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-notification-line",
            ],
            'name=:name',
            [':name' => 'notification']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-key-line",
            ],
            'name=:name',
            [':name' => 'publication']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "ri-lock-password-line",
            ],
            'name=:name',
            [':name' => 'surveypermissions']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'listQuestions']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'listQuestions']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'listQuestionGroups']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'reorder']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'participants']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'emailtemplates']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'failedemail']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'quotas']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'assessments']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'panelintegration']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'responses']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'statistics']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'resources']
        );

        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "menu_icon_type" => "remix",
                "menu_icon" => "",
            ],
            'name=:name',
            [':name' => 'plugins']
        );
    }
}
