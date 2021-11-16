            $aTableNames = dbGetTablesLike("tokens%");
            $oDB = Yii::app()->getDb();
            foreach ($aTableNames as $sTableName) {
                try {
                    setTransactionBookmark();
                    switch (Yii::app()->db->driverName) {
                        case 'mysql':
                        case 'mysqli':
                            try {
                                setTransactionBookmark();
                                $oDB->createCommand()->createIndex('idx_email', $sTableName, 'email(30)', false);
                            } catch (Exception $e) {
                                rollBackToTransactionBookmark();
                            }
                            break;
                        case 'pgsql':
                            try {
                                setTransactionBookmark();
                                $oDB->createCommand()->createIndex('idx_email', $sTableName, 'email', false);
                            } catch (Exception $e) {
                                rollBackToTransactionBookmark();
                            }
                            break;
                        // MSSQL does not support indexes on text fields so no dice
                    }
                } catch (Exception $e) {
                    rollBackToTransactionBookmark();
                }
            }
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 433], "stg_name='DBVersion'");
            $oTransaction->commit();
