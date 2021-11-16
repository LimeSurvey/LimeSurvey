            if (Yii::app()->db->driverName == 'mysql') {
                modifyPrimaryKey('questions', array('qid', 'language'));
            }
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 178), "stg_name='DBVersion'");
            $oTransaction->commit();
