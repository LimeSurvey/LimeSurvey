            $oDB->createCommand()->createTable(
                '{{asset_version}}',
                array(
                    'id' => 'pk',
                    'path' => 'text NOT NULL',
                    'version' => 'integer NOT NULL',
                )
            );
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 350], "stg_name='DBVersion'");
            $oTransaction->commit();
