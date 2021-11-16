            /*
            * The hash value of a notification is used to calculate uniqueness.
            * @since 2016-08-10
            * @author Olle Haerstedt
            */
            addColumn('{{notifications}}', 'hash', 'string(64)');
            $oDB->createCommand()->createIndex('{{notif_hash_index}}', '{{notifications}}', 'hash', false);
