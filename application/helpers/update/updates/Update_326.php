            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->alterColumn('{{surveys}}', 'datecreated', 'datetime');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 326), "stg_name='DBVersion'");
            $oTransaction->commit();
