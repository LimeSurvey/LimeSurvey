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
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 153), "stg_name='DBVersion'");
            $oTransaction->commit();
