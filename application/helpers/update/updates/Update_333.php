            $oTransaction = $oDB->beginTransaction();
            upgrade333($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 333), "stg_name='DBVersion'");
            $oTransaction->commit();
