<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_709 extends DatabaseUpdateBase
{
    /**
     * Correct the title and description of the "arrays/yesnouncertain" question theme to
     * match the rendered answer order (Yes / Uncertain / No). Only updates rows whose
     * current value still matches the previous string, leaving admin customisations intact.
     *
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        /* Delete existing columns, juts for dev git userd */
        dropColumn('{{surveys}}', 'crypt_method');
        dropColumn('{{surveys_groupsettings}}', 'crypt_method');
        /* Add crypt_method columns */
        addColumn('{{surveys}}', 'crypt_method', "string(1) DEFAULT 'I'");
        addColumn('{{surveys_groupsettings}}', 'crypt_method', "string(1) DEFAULT 'I'");
        /* Set global one to B (basic), didn't update any response table */
        $this->db->createCommand()->update("{{surveys_groupsettings}}", ["crypt_method" => "B"], "gsid = 0");
    }
}
