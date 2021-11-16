            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{question_attributes}}', 'value', 'text');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 135), "stg_name='DBVersion'");
            $oTransaction->commit();
