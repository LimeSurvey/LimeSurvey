            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables255();
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 255), "stg_name='DBVersion'");
            $oTransaction->commit();
