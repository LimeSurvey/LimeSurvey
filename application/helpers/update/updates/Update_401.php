
            $oDB->createCommand()->addColumn('{{plugins}}', 'load_error', 'int default 0');
            $oDB->createCommand()->addColumn('{{plugins}}', 'load_error_message', 'text');

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 401), "stg_name='DBVersion'");
