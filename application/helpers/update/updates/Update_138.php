            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{quota_members}}', 'code', "string(11)");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 138), "stg_name='DBVersion'");
            $oTransaction->commit();
