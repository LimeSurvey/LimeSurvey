            $oTransaction = $oDB->beginTransaction();
            upgradeTemplateTables304($oDB);
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 304), "stg_name='DBVersion'");
