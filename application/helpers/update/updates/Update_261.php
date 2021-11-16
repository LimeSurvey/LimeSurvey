            $oTransaction = $oDB->beginTransaction();
            /*
            * The hash value of a notification is used to calculate uniqueness.
            * @since 2016-08-10
            * @author Olle Haerstedt
            */
            addColumn('{{notifications}}', 'hash', 'string(64)');
            $oDB->createCommand()->createIndex('{{notif_hash_index}}', '{{notifications}}', 'hash', false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 261), "stg_name='DBVersion'");
            $oTransaction->commit();
