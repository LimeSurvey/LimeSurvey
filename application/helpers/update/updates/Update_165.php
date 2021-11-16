            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->createTable(
                '{{plugins}}',
                array(
                    'id' => 'pk',
                    'name' => 'string NOT NULL',
                    'active' => 'boolean'
                )
            );
            $oDB->createCommand()->createTable(
                '{{plugin_settings}}',
                array(
                    'id' => 'pk',
                    'plugin_id' => 'integer NOT NULL',
                    'model' => 'string',
                    'model_id' => 'integer',
                    'key' => 'string',
                    'value' => 'text'
                )
            );
            alterColumn('{{surveys_languagesettings}}', 'surveyls_url', "text");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 165), "stg_name='DBVersion'");
            $oTransaction->commit();
