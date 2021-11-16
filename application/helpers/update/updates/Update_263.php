            // Dummy version update for hash column in installation SQL.
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 263), "stg_name='DBVersion'");
            $oTransaction->commit();
