            if (Yii::app()->db->driverName == 'mysql') {
                modifyPrimaryKey('questions', array('qid', 'language'));
            }
