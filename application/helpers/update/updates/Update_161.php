            addColumn('{{survey_links}}', 'date_invited', 'datetime NULL default NULL');
            addColumn('{{survey_links}}', 'date_completed', 'datetime NULL default NULL');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 161), "stg_name='DBVersion'");
            $oTransaction->commit();
