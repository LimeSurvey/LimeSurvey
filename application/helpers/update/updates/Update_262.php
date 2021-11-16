            alterColumn('{{settings_global}}', 'stg_value', "text", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 262), "stg_name='DBVersion'");
            $oTransaction->commit();
