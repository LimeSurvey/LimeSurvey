            $oTransaction = $oDB->beginTransaction();


            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 294), "stg_name='DBVersion'");
            $oTransaction->commit();
