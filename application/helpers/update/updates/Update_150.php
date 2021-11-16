            $oTransaction = $oDB->beginTransaction();
            addColumn('{{questions}}', 'relevance', 'text');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 150), "stg_name='DBVersion'");
            $oTransaction->commit();
