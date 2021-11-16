            $aTableNames = dbGetTablesLike("tokens%");
            $oDB = Yii::app()->getDb();
            foreach ($aTableNames as $sTableName) {
                try {
                    setTransactionBookmark();
                    switch (Yii::app()->db->driverName) {
                        case 'mysql':
                        case 'mysqli':
                            $oDB->createCommand()->createIndex('idx_email', $sTableName, 'email(30)', false);
                            break;
                        case 'pgsql':
                            $oDB->createCommand()->createIndex(
                                'idx_email_' . substr($sTableName, 7) . '_' . rand(1, 50000),
                                $sTableName,
                                'email',
                                false
                            );
                            break;
                        // MSSQL does not support indexes on text fields so no dice
                    }
                } catch (Exception $e) {
                    rollBackToTransactionBookmark();
                }
            }
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 363], "stg_name='DBVersion'");
            $oTransaction->commit();
