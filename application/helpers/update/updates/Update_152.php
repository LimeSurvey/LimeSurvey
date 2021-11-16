            $oDB->createCommand()->createIndex('question_attributes_idx3', '{{question_attributes}}', 'attribute');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 152), "stg_name='DBVersion'");
            $oTransaction->commit();
