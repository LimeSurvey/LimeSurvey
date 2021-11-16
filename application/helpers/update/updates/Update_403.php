            upgradeTokenTables402('utf8mb4_bin');
            upgradeSurveyTables402('utf8mb4_bin');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 403), "stg_name='DBVersion'");
            $oTransaction->commit();
