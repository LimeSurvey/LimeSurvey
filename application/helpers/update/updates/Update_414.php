            $oDB->createCommand()->addColumn('{{users}}', 'lastLogin', "datetime NULL");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 414), "stg_name='DBVersion'");
            $oTransaction->commit();
