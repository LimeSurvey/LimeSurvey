<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_641 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * Update description of bootswatch and bootswatch childs to open link in new windows
     * @throws CException
     */
    public function up()
    {
        /* @var string the new description */
        $newdescription = "{{gT(\"Based on BootsWatch Themes:\")}}<br><a href='https://bootswatch.com/3/' target='_blank' rel='external' title='{{gT(\"Visit Bootswatch page in a new window.\")}}'>{{gT(\"Visit Bootswatch page\")}} <i class='ri-external-link-line'></i><span class='visually-hidden'>{{gT(\"(Opens in a new window)\")}}</span></a>":
        // Update core theme
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
        // Update extended theme based on description
        $this->db->createCommand()->update(
            "{{templates}}",
            [
                'description' => $newdescription
            ],
            "description = :olddescription",
            [
                // Done by XML
                ':olddescription' => "{{gT(\"Based on BootsWatch Themes:\")}} <br><a href='https://bootswatch.com/3/'>{{gT(\"Visit Bootswatch page\")}}</a> "
            ]
        );
        $this->db->createCommand()->update(
            "{{templates}}",
            [
                'description' => $newdescription
            ],
            "description = :olddescription",
            [
                // Done when install via LsDefaultDataSets : if superadmin language was updated : didn't update
                ':olddescription' => gT("Based on BootsWatch Themes:") . "<br><a href='https://bootswatch.com/3/'>" . gT("Visit Bootswatch page") . "</a> "
            ]
        );
    }
}
