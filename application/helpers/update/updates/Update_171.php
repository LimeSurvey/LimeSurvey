            $oTransaction = $oDB->beginTransaction();
            try {
                dropColumn('{{sessions}}', 'data');
            } catch (Exception $e) {
            }
            switch (Yii::app()->db->driverName) {
                case 'mysql':
                    addColumn('{{sessions}}', 'data', 'longbinary');
                    break;
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                    addColumn('{{sessions}}', 'data', 'VARBINARY(MAX)');
                    break;
                case 'pgsql':
                    addColumn('{{sessions}}', 'data', 'BYTEA');
                    break;
            }
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 171), "stg_name='DBVersion'");
            $oTransaction->commit();
