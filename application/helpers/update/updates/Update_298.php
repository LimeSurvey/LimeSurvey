            $oTransaction = $oDB->beginTransaction();
            upgradeTemplateTables298($oDB);
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 298), "stg_name='DBVersion'");
