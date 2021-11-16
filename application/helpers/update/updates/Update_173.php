            addColumn('{{participant_attribute_names}}', 'defaultname', "string(50) NOT NULL default ''");
            upgradeCPDBAttributeDefaultNames173();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 173), "stg_name='DBVersion'");
            $oTransaction->commit();
