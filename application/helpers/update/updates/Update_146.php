<?php

namespace LimeSurvey\Helpers\Update;

class Update_146 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeSurveyTimings146();
            // Fix permissions for new feature quick-translation
            try {
                setTransactionBookmark();
                $oDB->createCommand(
                    "INSERT into {{survey_permissions}} (sid,uid,permission,read_p,update_p) SELECT sid,owner_id,'translations','1','1' from {{surveys}}"
                )->execute();
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
    }
}