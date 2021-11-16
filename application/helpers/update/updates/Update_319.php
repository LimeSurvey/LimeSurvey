            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 319), "stg_name='DBVersion'");

            $table = Yii::app()->db->schema->getTable('{{surveys_groups}}');
            if (isset($table->columns['order'])) {
                $oDB->createCommand()->renameColumn('{{surveys_groups}}', 'order', 'sortorder');
            }

            $table = Yii::app()->db->schema->getTable('{{templates}}');
            if (isset($table->columns['extends_template_name'])) {
                $oDB->createCommand()->renameColumn('{{templates}}', 'extends_template_name', 'extends');
            }

            $oTransaction->commit();
