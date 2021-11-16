            upgradeTokenTables181('utf8_bin');
            upgradeSurveyTables181('utf8_bin');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 181), "stg_name='DBVersion'");
            $oTransaction->commit();
