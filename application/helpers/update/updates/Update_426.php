<?php

namespace LimeSurvey\Helpers\Update;

class Update_426 extends DatabaseUpdateBase
{
    public function up()
    {

            $this->db->createCommand()->addColumn(
                '{{surveys_groupsettings}}',
                'ipanonymize',
                "string(1) NOT NULL DEFAULT 'N'"
            );
            $this->db->createCommand()->addColumn('{{surveys}}', 'ipanonymize', "string(1) NOT NULL DEFAULT 'N'");

            //all groups (except default group gsid=0), must have inheritance value
            $this->db->createCommand()->update('{{surveys_groupsettings}}', array('ipanonymize' => 'I'), 'gsid<>0');

            //change gsid=1 for inheritance logic ...(redundant, but for better understanding and securit)
            $this->db->createCommand()->update('{{surveys_groupsettings}}', array('ipanonymize' => 'I'), 'gsid=1');

            //for all non active surveys,the value must be "I" for inheritance ...
            $this->db->createCommand()->update('{{surveys}}', array('ipanonymize' => 'I'), "active='N'");
    }
}
