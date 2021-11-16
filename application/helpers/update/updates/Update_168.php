            addColumn('{{participants}}', 'created', 'datetime');
            addColumn('{{participants}}', 'modified', 'datetime');
            addColumn('{{participants}}', 'created_by', 'integer');
            $oDB->createCommand('update {{participants}} set created_by=owner_uid')->query();
            alterColumn('{{participants}}', 'created_by', "integer", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 168), "stg_name='DBVersion'");
            $oTransaction->commit();
