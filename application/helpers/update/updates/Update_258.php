            $oTransaction = $oDB->beginTransaction();
            Yii::app()->getDb()->createCommand(
                "DELETE FROM {{settings_global}} WHERE stg_name='adminimageurl'"
            )->execute();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 258), "stg_name='DBVersion'");
            $oTransaction->commit();
