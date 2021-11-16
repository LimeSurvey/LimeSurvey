            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->addColumn('{{groups}}', 'grelevance', "text");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 154), "stg_name='DBVersion'");
            $oTransaction->commit();
