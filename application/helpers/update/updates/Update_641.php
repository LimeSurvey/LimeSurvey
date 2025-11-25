<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_641 extends DatabaseUpdateBase
{
    /**
     * Update bootswatch-related template descriptions so the Bootswatch link opens in a new window.
     *
     * Updates the core "bootswatch" template description and any templates whose description matches
     * the previous BootsWatch description to a new HTML description that includes target="_blank"
     * and accessible text for external links.
     *
     * @throws CException If a database update operation fails.
     */
    public function up()
    {
        /* @var string the new description */
        $newdescription = "{{gT(\"Based on BootsWatch Themes:\")}}<br><a href='https://bootswatch.com/3/' target='_blank' rel='external' title='{{gT(\"Visit Bootswatch page in a new window.\")}}'>{{gT(\"Visit Bootswatch page\")}} <i class='ri-external-link-line'></i><span class='visually-hidden'>{{gT(\"(Opens in a new window)\")}}</span></a>";
        // Update core theme : always since it's core
        $this->db->createCommand()->update(
            "{{templates}}",
            [
                'description' => $newdescription
            ],
            "name = :name",
            [
                ':name' => 'bootswatch'
            ]
        );
        // Update extended theme based on description : can be updated via XML file before import. The description use XML tiwg file directly
        $this->db->createCommand()->update(
            "{{templates}}",
            [
                'description' => $newdescription
            ],
            "description = :olddescription",
            [
                ':olddescription' => "{{gT(\"Based on BootsWatch Themes:\")}} <br><a href='https://bootswatch.com/3/'>{{gT(\"Visit Bootswatch page\")}}</a> "
            ]
        );
    }
}