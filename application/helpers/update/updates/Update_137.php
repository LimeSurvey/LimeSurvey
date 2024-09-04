<?php

namespace LimeSurvey\Helpers\Update;

class Update_137 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{surveys_languagesettings}}', 'surveyls_dateformat', "integer NOT NULL DEFAULT 1");
            addColumn('{{users}}', 'dateformat', "integer NOT NULL DEFAULT 1");
            $this->db->createCommand()->update('{{surveys}}', array('startdate' => null), "usestartdate='N'");
            $this->db->createCommand()->update('{{surveys}}', array('expires' => null), "useexpiry='N'");
            dropColumn('{{surveys}}', 'useexpiry');
            dropColumn('{{surveys}}', 'usestartdate');
    }
}
