            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{failed_login_attempts}}', 'ip', "string(40)", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 159), "stg_name='DBVersion'");
            $oTransaction->commit();
