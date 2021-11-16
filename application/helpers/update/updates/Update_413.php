             *  changes for this version are removed, but this block stays for the continuity
             */

            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 413), "stg_name='DBVersion'");
            $oTransaction->commit();
