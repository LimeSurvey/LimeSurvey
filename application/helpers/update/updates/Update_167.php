            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys_languagesettings}}', 'attachments', 'text');
            addColumn('{{users}}', 'created', 'datetime');
            addColumn('{{users}}', 'modified', 'datetime');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 167), "stg_name='DBVersion'");
            $oTransaction->commit();
