            $oDB->createCommand()->addColumn('{{tutorials}}', 'title', 'string(192)');
            $oDB->createCommand()->addColumn('{{tutorials}}', 'icon', 'string(64)');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 334), "stg_name='DBVersion'");
            $oTransaction->commit();
