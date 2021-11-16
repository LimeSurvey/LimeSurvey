            upgradeTokens148(); // this should have bee done in 148 - that's why it is named this way
            // fix survey tables for missing or incorrect token field
            upgradeSurveyTables164();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 164), "stg_name='DBVersion'");
            $oTransaction->commit();
            // Not updating settings table as upgrade process takes care of that step now
