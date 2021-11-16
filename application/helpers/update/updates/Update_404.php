            $oTransaction = $oDB->beginTransaction();
            createSurveysGroupSettingsTable($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 404), "stg_name='DBVersion'");
            $oTransaction->commit();
