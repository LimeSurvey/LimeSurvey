<?php

namespace LimeSurvey\Helpers\Update;

use SurveysGroupsettings;

class Update_412 extends DatabaseUpdateBase
{
    public function up()
    {
        $sSurveyGroupQuery = "SELECT gsid  from {{surveys_groups}} order by gsid";
        $aGroups = $this->db->createCommand($sSurveyGroupQuery)->queryColumn();
        $sSurveyGroupSettingsQuery = "SELECT gsid  from {{surveys_groupsettings}} order by gsid";
        $aGroupSettings = $this->db->createCommand($sSurveyGroupSettingsQuery)->queryColumn();
        foreach ($aGroups as $group) {
            if (!array_key_exists($group, $aGroupSettings)) {
                $settings = new SurveysGroupsettings();
                $settings->setToInherit();
                $settings->gsid = $group;
                $this->db->createCommand()->insert("{{surveys_groupsettings}}", $settings->attributes);
            }
        }
    }
}
