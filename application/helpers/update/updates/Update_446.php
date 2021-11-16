            // archived_table_settings
            $oDB->createCommand()->createTable(
                '{{archived_table_settings}}',
                [
                    'id' => "pk",
                    'survey_id' => "int NOT NULL",
                    'user_id' => "int NOT NULL",
                    'tbl_name' => "string(255) NOT NULL",
                    'tbl_type' => "string(10) NOT NULL",
                    'created' => "datetime NOT NULL",
                    'properties' => "text NOT NULL",
                ],
                $options
            );
            upgradeArchivedTableSettings446();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 446), "stg_name='DBVersion'");
