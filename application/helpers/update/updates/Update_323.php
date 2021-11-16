            dropPrimaryKey('labels', 'lid');
            $oDB->createCommand()->addColumn('{{labels}}', 'id', 'pk');
            $oDB->createCommand()->createIndex(
                '{{idx4_labels}}',
                '{{labels}}',
                ['lid', 'sortorder', 'language'],
                false
            );
