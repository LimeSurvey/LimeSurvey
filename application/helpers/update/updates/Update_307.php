            $oTransaction = $oDB->beginTransaction();
            if (tableExists('{settings_user}')) {
                $oDB->createCommand()->dropTable('{{settings_user}}');
            }
            $oDB->createCommand()->createTable(
                '{{settings_user}}',
                array(
                    'id' => 'pk',
                    'uid' => 'integer NOT NULL',
                    'entity' => 'string(15)',
                    'entity_id' => 'string(31)',
                    'stg_name' => 'string(63) not null',
                    'stg_value' => 'text',

                )
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 307), "stg_name='DBVersion'");
            $oTransaction->commit();
