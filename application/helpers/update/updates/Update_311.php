            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys_groups}}', 'template', "string(128) DEFAULT 'default'");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 311), "stg_name='DBVersion'");
            $oTransaction->commit();
