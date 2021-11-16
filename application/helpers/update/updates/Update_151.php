            $oTransaction = $oDB->beginTransaction();
            addColumn('{{groups}}', 'randomization_group', "string(20) NOT NULL default ''");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 151), "stg_name='DBVersion'");
            $oTransaction->commit();
