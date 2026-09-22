<?php

namespace LimeSurvey\Helpers\Update;

class Update_712 extends DatabaseUpdateBase
{
    /**
     * Keep the historic preselected "No answer" behaviour for upgraded installations.
     * Fresh installations use N in installer/create-database.php.
     *
     * @inheritDoc
     */
    public function up()
    {
        addColumn('{{surveys}}', 'preselectnoanswer', "string(1) NULL DEFAULT 'I'");
        addColumn('{{surveys_groupsettings}}', 'preselectnoanswer', "string(1) NULL DEFAULT 'I'");

        $this->db->createCommand()->update(
            '{{surveys_groupsettings}}',
            ['preselectnoanswer' => 'Y'],
            'gsid = :gsid',
            [':gsid' => 0]
        );
    }
}
