            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables254();
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 254), "stg_name='DBVersion'");
            $oTransaction->commit();
