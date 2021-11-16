            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{answers}}', 'assessment_value', 'integer', false, '0');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 343), "stg_name='DBVersion'");
            $oTransaction->commit();
