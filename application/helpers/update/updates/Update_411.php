            $oDB->createCommand()->addColumn('{{plugins}}', 'priority', "int NOT NULL default 0");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 411), "stg_name='DBVersion'");
            $oTransaction->commit();
