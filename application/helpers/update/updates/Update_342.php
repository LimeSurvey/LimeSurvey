            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 342), "stg_name='DBVersion'");
            $oTransaction->commit();
