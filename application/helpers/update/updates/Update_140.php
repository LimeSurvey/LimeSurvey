            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys}}', 'emailresponseto', 'text');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 140), "stg_name='DBVersion'");
            $oTransaction->commit();
