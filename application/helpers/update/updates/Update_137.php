<?php

namespace LimeSurvey\Helpers\Update;

class Update_137 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{surveys_languagesettings}}', 'surveyls_dateformat', "integer NOT NULL default 1");
            addColumn('{{users}}', 'dateformat', "integer NOT NULL default 1");
            $oDB->createCommand()->update('{{surveys}}', array('startdate' => null), "usestartdate='N'");
            $oDB->createCommand()->update('{{surveys}}', array('expires' => null), "useexpiry='N'");
            dropColumn('{{surveys}}', 'useexpiry');
            dropColumn('{{surveys}}', 'usestartdate');
    }
}