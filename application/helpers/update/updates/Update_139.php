            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables139();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 139), "stg_name='DBVersion'");
            $oTransaction->commit();
