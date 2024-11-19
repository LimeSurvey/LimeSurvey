<?php

namespace LimeSurvey\Helpers\Update;

use LsDefaultDataSets;
use SurveymenuEntries;

class Update_490 extends DatabaseUpdateBase
{
    /**
     * This table is needed to collect failed emails.
     */
    public function up()
    {
        $this->db->createCommand()->update("{{surveymenu_entries}}", ['title' => 'Privacy policy settings', 'menu_title' => 'Privacy policy', 'menu_description' => 'Edit privacy policy settings'], "name='datasecurity'");
        $this->db->createCommand()->dropColumn('{{surveys}}', 'faxto');
    }
}
