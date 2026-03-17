<?php

namespace LimeSurvey\Helpers\Update;

class Update_702 extends DatabaseUpdateBase
{
    /**
     * Remove nokeyboard column from surveys and surveys_group_settings tables.
     * The on-screen keyboard functionality has been deprecated as modern systems
     * provide native virtual keyboards at the OS/browser level.
     */
    public function up()
    {
        // Drop nokeyboard column from surveys table
        dropColumn('{{surveys}}', 'nokeyboard');

        // Drop nokeyboard column from surveys_group_settings table
        dropColumn('{{surveys_group_settings}}', 'nokeyboard');
    }
}
