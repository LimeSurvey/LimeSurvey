            addColumn('{{quota}}', 'autoload_url', "integer NOT NULL default 0");
            // Create quota table
            $aFields = array(
                'quotals_id' => 'pk',
                'quotals_quota_id' => 'integer NOT NULL DEFAULT 0',
                'quotals_language' => "string(45) NOT NULL default 'en'",
                'quotals_name' => 'string',
                'quotals_message' => 'text NOT NULL',
                'quotals_url' => 'string',
                'quotals_urldescrip' => 'string',
            );
            $oDB->createCommand()->createTable('{{quota_languagesettings}}', $aFields);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 136), "stg_name='DBVersion'");
            $oTransaction->commit();
