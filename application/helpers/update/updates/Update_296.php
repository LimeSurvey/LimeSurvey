

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 296), "stg_name='DBVersion'");
            $oTransaction->commit();
