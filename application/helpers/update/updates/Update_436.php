            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{boxes}}', array('url' => 'themeOptions'), "url='admin/themeoptions'");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 436), "stg_name='DBVersion'");
            $oTransaction->commit();
