            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 321), "stg_name='DBVersion'");
            $oTransaction->commit();
