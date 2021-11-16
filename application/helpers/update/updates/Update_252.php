            Yii::app()->db->createCommand()->addColumn('{{questions}}', 'modulename', 'string');
            // Update DBVersion
