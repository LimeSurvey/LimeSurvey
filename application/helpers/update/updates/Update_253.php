            $oTransaction = $oDB->beginTransaction();
            upgradeSurveyTables253();

            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 253), "stg_name='DBVersion'");
            $oTransaction->commit();
