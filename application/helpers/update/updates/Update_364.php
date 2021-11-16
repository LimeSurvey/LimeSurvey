            $oTransaction = $oDB->beginTransaction();
            extendDatafields364($oDB);
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 364], "stg_name='DBVersion'");
            $oTransaction->commit();
