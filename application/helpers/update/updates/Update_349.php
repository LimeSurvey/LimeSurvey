            $oTransaction = $oDB->beginTransaction();
            dropColumn('{{users}}', 'one_time_pw');
            addColumn('{{users}}', 'one_time_pw', 'text');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 349], "stg_name='DBVersion'");
            $oTransaction->commit();
