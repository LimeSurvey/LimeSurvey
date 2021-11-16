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
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 146), "stg_name='DBVersion'");
            $oTransaction->commit();
