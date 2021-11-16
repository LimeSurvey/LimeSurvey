            $oTransaction = $oDB->beginTransaction();
            upgrade328($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 328), "stg_name='DBVersion'");
            $oTransaction->commit();
