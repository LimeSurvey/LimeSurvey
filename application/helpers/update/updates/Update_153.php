            $oDB->createCommand()->createTable(
                '{{expression_errors}}',
                array(
                    'id' => 'pk',
                    'errortime' => 'string(50)',
                    'sid' => 'integer',
                    'gid' => 'integer',
                    'qid' => 'integer',
                    'gseq' => 'integer',
                    'qseq' => 'integer',
                    'type' => 'string(50)',
                    'eqn' => 'text',
                    'prettyprint' => 'text'
                )
            );
