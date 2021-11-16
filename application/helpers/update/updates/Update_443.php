            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->renameColumn('{{users}}', 'lastLogin', 'last_login');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 443), "stg_name='DBVersion'");
            $oTransaction->commit();
