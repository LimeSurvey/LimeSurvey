            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys}}', 'tokenlength', 'integer NOT NULL default 15');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 141), "stg_name='DBVersion'");
            $oTransaction->commit();
