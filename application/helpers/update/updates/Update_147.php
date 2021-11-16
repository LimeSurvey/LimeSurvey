            addColumn('{{users}}', 'templateeditormode', "string(7) NOT NULL default 'default'");
            addColumn('{{users}}', 'questionselectormode', "string(7) NOT NULL default 'default'");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 147), "stg_name='DBVersion'");
            $oTransaction->commit();
