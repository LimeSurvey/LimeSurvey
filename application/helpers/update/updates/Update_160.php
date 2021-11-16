            $oTransaction = $oDB->beginTransaction();
            alterLanguageCode('it', 'it-informal');
            alterLanguageCode('it-formal', 'it');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 160), "stg_name='DBVersion'");
            $oTransaction->commit();
