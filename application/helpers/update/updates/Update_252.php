            $oTransaction = $oDB->beginTransaction();
            Yii::app()->db->createCommand()->addColumn('{{questions}}', 'modulename', 'string');
            // Update DBVersion
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 252), "stg_name='DBVersion'");
            $oTransaction->commit();
