            $oTransaction = $oDB->beginTransaction();
            dropPrimaryKey('labels', 'lid');
            $oDB->createCommand()->addColumn('{{labels}}', 'id', 'pk');
            $oDB->createCommand()->createIndex(
                '{{idx4_labels}}',
                '{{labels}}',
                ['lid', 'sortorder', 'language'],
                false
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 323), "stg_name='DBVersion'");
            $oTransaction->commit();
