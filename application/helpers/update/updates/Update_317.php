
            transferPasswordFieldToText($oDB);

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 317), "stg_name='DBVersion'");
            $oTransaction->commit();
