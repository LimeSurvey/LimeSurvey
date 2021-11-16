            fixKCFinder184();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 184), "stg_name='DBVersion'");
            $oTransaction->commit();
