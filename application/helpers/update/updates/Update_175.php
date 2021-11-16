            $oTransaction = $oDB->beginTransaction();
            switch (Yii::app()->db->driverName) {
                case 'pgsql':
                    // Special treatment for Postgres as it is too dumb to convert a boolean to a number without explicit being told to do so
                    alterColumn('{{plugins}}', 'active', "INTEGER USING (active::integer)", false);
                    break;
                default:
                    alterColumn('{{plugins}}', 'active', "integer", false, '0');
            }
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 175), "stg_name='DBVersion'");
            $oTransaction->commit();
