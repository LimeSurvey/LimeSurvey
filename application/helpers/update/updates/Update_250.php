            createBoxes250();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 250), "stg_name='DBVersion'");
            $oTransaction->commit();
