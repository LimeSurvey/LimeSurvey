            $oTransaction = $oDB->beginTransaction();
            upgradeQuestionAttributes142();
            $oDB->createCommand()->alterColumn('{{surveys}}', 'expires', "datetime");
            $oDB->createCommand()->alterColumn('{{surveys}}', 'startdate', "datetime");
            $oDB->createCommand()->update('{{question_attributes}}', array('value' => 0), "value='false'");
            $oDB->createCommand()->update('{{question_attributes}}', array('value' => 1), "value='true'");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 142), "stg_name='DBVersion'");
            $oTransaction->commit();
