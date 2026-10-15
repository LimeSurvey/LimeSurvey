<?php

namespace LimeSurvey\Helpers\Update;

class Update_412 extends DatabaseUpdateBase
{
    /**
     * Backfills missing surveys_groupsettings rows for existing survey groups,
     * with all inheritable attributes set to "inherit" ('I'/-1/'inherit').
     *
     * NOTE: this must not use the SurveysGroupsettings model. Its setToInherit()
     * method reflects the *current* table schema, which at this point in the
     * update chain does not yet have the ipanonymize, showregisterpolicy,
     * showtokenpolicy or preselectnoanswer columns (added in later updates).
     * The attribute list below mirrors the surveys_groupsettings table as it
     * existed when this update runs.
     *
     * @return void
     */
    #[\Override]
    public function up()
    {
        $sSurveyGroupQuery = "SELECT gsid  from {{surveys_groups}} order by gsid";
        $aGroups = $this->db->createCommand($sSurveyGroupQuery)->queryColumn();
        $sSurveyGroupSettingsQuery = "SELECT gsid  from {{surveys_groupsettings}} order by gsid";
        $aGroupSettings = $this->db->createCommand($sSurveyGroupSettingsQuery)->queryColumn();
        foreach ($aGroups as $group) {
            if (!array_key_exists($group, $aGroupSettings)) {
                $attributes = array(
                    'gsid' => $group,
                    'owner_id' => -1,
                    'admin' => 'inherit',
                    'adminemail' => 'inherit',
                    'anonymized' => 'I',
                    'format' => 'I',
                    'savetimings' => 'I',
                    'template' => 'inherit',
                    'datestamp' => 'I',
                    'usecookie' => 'I',
                    'allowregister' => 'I',
                    'allowsave' => 'I',
                    'autonumber_start' => 0,
                    'autoredirect' => 'I',
                    'allowprev' => 'I',
                    'printanswers' => 'I',
                    'ipaddr' => 'I',
                    'refurl' => 'I',
                    'showsurveypolicynotice' => 0,
                    'publicstatistics' => 'I',
                    'publicgraphs' => 'I',
                    'listpublic' => 'I',
                    'htmlemail' => 'I',
                    'sendconfirmation' => 'I',
                    'tokenanswerspersistence' => 'I',
                    'assessments' => 'I',
                    'usecaptcha' => 'E',
                    'bounce_email' => 'inherit',
                    'attributedescriptions' => null,
                    'emailresponseto' => 'inherit',
                    'emailnotificationto' => 'inherit',
                    'tokenlength' => -1,
                    'showxquestions' => 'I',
                    'showgroupinfo' => 'I',
                    'shownoanswer' => 'I',
                    'showqnumcode' => 'I',
                    'showwelcome' => 'I',
                    'showprogress' => 'I',
                    'questionindex' => -1,
                    'navigationdelay' => -1,
                    'alloweditaftercompletion' => 'I',
                );
                $this->db->createCommand()->insert("{{surveys_groupsettings}}", $attributes);
            }
        }
    }
}
