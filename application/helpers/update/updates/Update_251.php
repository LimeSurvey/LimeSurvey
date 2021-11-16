            $oTransaction = $oDB->beginTransaction();
            upgradeBoxesTable251();

            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 251), "stg_name='DBVersion'");
            $oTransaction->commit();
