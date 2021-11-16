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
