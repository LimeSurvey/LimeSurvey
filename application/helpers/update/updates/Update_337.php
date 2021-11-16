            resetTutorials337($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 337), "stg_name='DBVersion'");
            $oTransaction->commit();
