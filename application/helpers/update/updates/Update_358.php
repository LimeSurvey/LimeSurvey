            $oTransaction = $oDB->beginTransaction();
            dropColumn('{{sessions}}', 'data');
            addColumn('{{sessions}}', 'data', 'longbinary');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 358], "stg_name='DBVersion'");
            $oTransaction->commit();
