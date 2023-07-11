<?php

namespace LimeSurvey\Helpers\Update;

class Update_609 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->update(
            "{{settings_global}}",
            ['stg_value' => 'fruity_twentythree'],
            "stg_name = :stg_name",
            [':stg_name' => 'defaulttheme']
        );
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
        $this->db->createCommand()->update(
            "{{templates}}",
            [
                'title'       => 'Fruity TwentyThree',
                'description' => gT("Our default theme for a fruity and flexible use. This theme offers single color variations")
            ],
            "name = :name",
            [':name' => 'fruity_twentythree']
        );
    }
}
