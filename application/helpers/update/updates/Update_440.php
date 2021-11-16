            $oDB->createCommand()->createIndex('sess_expire', '{{sessions}}', 'expire');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 440], "stg_name='DBVersion'");
            $oTransaction->commit();
