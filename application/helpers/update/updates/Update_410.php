            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->addColumn('{{question_l10ns}}', 'script', " text NULL default NULL");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 410), "stg_name='DBVersion'");
            $oTransaction->commit();
