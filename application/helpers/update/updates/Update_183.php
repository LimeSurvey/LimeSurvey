            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables183();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 183), "stg_name='DBVersion'");
            $oTransaction->commit();
