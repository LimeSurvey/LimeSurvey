            Yii::app()->getDb()->createCommand(
                "DELETE FROM {{settings_global}} WHERE stg_name='adminimageurl'"
            )->execute();
